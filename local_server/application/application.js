const path = require('path');
const fs = require('fs');
const multer = require('multer');
const crypto = require('crypto');

const { db } = require('../utils/mysql_entry');
const { find_user_in_db } = require('../users/profile');
const { parse_jwt_token } = require('../utils/basic_tools');

// 設定 multer 儲存位置
const USER_UPLOADED_DOCUMENT_STORE_PLACE = process.env.USER_UPLOADED_DOCUMENT_STORE_PLACE;
if (!fs.existsSync(USER_UPLOADED_DOCUMENT_STORE_PLACE)) fs.mkdirSync(USER_UPLOADED_DOCUMENT_STORE_PLACE, { recursive: true });

const storage = multer.diskStorage({
    destination: (req, file, cb) => cb(null, USER_UPLOADED_DOCUMENT_STORE_PLACE),
    filename: (req, file, cb) => {
        // 取原檔案副檔名
        const ext = path.extname(file.originalname); // 例: .pdf
        // 生成 10 位隨機英數字
        const randomName = crypto.randomBytes(5).toString('hex'); // 5 bytes -> 10 hex 字元
        // 最終檔名
        const safeName = Date.now() + '_' + randomName + ext;
        cb(null, safeName);
    }
});

const application_entry = async (req, res) => {
    if (req.query.detail) load_detail(req, res);
    else if (req.query.list) list(req, res);
    else if (req.query.upload) upload(req, res);
    else return res.status(400).json({ success: false, message: '未指定動作' });
}

const list = async (req, res) => {
    try {
        let query = `
            SELECT id, name, description, add_date, expires
            FROM all_applications
        `;

        // 沒有 all 參數 -> 限制條件
        if (!req.query.all) {
            query += ` WHERE expires IS NULL OR expires >= NOW()`;
        }

        const [applications] = await db.execute(query);

        res.json({
            success: true,
            applications: applications
        });
    } catch (err) {
        console.error(err);
        res.status(500).json({ success: false, message: '伺服器錯誤' });
    }
};

const load_detail = async (req, res) => {
    const id = req.query.id;
    if (!id) return res.status(400).json({ success: false, message: '未指定申請作業 ID' });

    try {
        // 1. 查詢申請作業
        const [applications] = await db.execute(
            `SELECT id, name, description, add_date, expires
             FROM all_applications
             WHERE id = ?`,
            [id]
        );

        if (applications.length === 0) {
            return res.status(404).json({ success: false, message: '找不到申請作業' });
        }

        const application = applications[0];

        // 2. 查詢所有關聯檔案
        const [files] = await db.execute(
            `SELECT f.id, f.name, f.create_date
             FROM open_files f
             JOIN application_file_link l ON f.id = l.document_id
             WHERE l.application_id = ?`,
            [id]
        );

        // 3. 回傳結果
        res.json({
            success: true,
            application: {
                ...application,
                files: files
            }
        });

    } catch (err) {
        console.error(err);
        res.status(500).json({ success: false, message: '伺服器錯誤' });
    }
};

const uploadMiddleware = multer({ storage }).array('files', 10); // 最多 10 個檔案

const upload = async (req, res) => { 
    const user = parse_jwt_token(req);
    if (user.userId == 'None') return res.status(401).json({ status: 1, error: 'Invalid jwt token' }); 
    else { 
        const userData = await find_user_in_db(user.userId); 
        // 解析表單資料
        uploadMiddleware(req, res, async (err) => {
            if (err) return res.status(500).json({ success: false, message: '檔案上傳失敗', error: err.message });

            const applicationId = req.query.applicationId; // 前端必須傳 applicationId
            if (!applicationId) return res.status(400).json({ success: false, message: '未指定申請作業 ID' });

            try {
                const files = req.files; // multer 解析後的檔案陣列
                if (!files || files.length === 0) return res.status(400).json({ success: false, message: '未上傳任何檔案' });

                // Insert into user_applications
                const [result1] = await db.execute(
                    `INSERT INTO user_applications (application_id, real_name, stu_id) VALUES (?, ?, ?)`,
                    [applicationId, userData.real_name, userData.stu_id] // description 可依需求填寫
                );
                const user_application_id = result1.insertId;

                for (const f of files) {
                    // 1. insert into signed_files
                    const [result2] = await db.execute(
                        `INSERT INTO signed_files (name, path, real_name, stu_id) VALUES (?, ?, ?, ?)`,
                        [Buffer.from(f.originalname, 'latin1').toString('utf8'), f.filename, userData.real_name, userData.stu_id] // description 可依需求填寫
                    );
                    const fileId = result2.insertId;

                    // 2. insert into application_file_link
                    await db.execute(
                        `INSERT INTO user_applications_file_link (application_id, document_id) VALUES (?, ?)`,
                        [user_application_id, fileId]
                    );
                }

                res.json({ success: true, message: '檔案上傳成功' });

            } catch (err) {
                console.error(err);
                res.status(500).json({ success: false, message: '伺服器錯誤', error: err.message });
            }
        });
    } 
}

module.exports = {
    application_entry
};