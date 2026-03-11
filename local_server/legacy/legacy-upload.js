const multer = require('multer');
const path = require('path');
const { db } = require('../utils/mysql_entry');
const { _check_access } = require('./access');
const { find_user_in_db } = require('../users/profile');
const { parse_jwt_token } = require('../utils/basic_tools');

// 配置 multer 儲存路徑與檔名
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, process.env.LEGACY_STORE_PLACE); // 確保 uploads 資料夾存在
  },
  filename: (req, file, cb) => {
    const unique = Date.now() + '-' + Math.round(Math.random() * 1E9);
    const ext = path.extname(file.originalname);
    cb(null, unique + ext);
  }
});

const upload = multer({ storage });

const safeValue = (val) => (typeof val === 'undefined' ? null : val); // 將 undefinded 的元素變成 null

// 接收 multipart/form-data，欄位 + 檔案
// ❗這是 Express middleware 包起來後的 handler
const legacy_upload = async (req, res) => {
  const have_access = await _check_access(req);
  if (have_access === 1) return res.status(401).json({ status: 1, error: 'Invalid jwt token' });

  upload.single('file')(req, res, async function (err) {
    if (err) {
      return res.status(400).json({ success: false, message: '檔案上傳錯誤', error: err.message });
    }

    try {
      const file = req.file;
      if (!file) return res.status(400).json({ success: false, message: '未上傳任何檔案', error: err.message });
      const {
        name, year, grade, semester,
        teacher, subject, course_code,
        type, description
      } = req.body;

      const user = await find_user_in_db(parse_jwt_token(req).userId);

      console.log(req.body);
      const tags = req.body['tags'];

      const values = [
        name, year, grade, semester,
        teacher, subject, course_code, type,
        description,
        file.path,
        user.stu_id, user.real_name
      ].map(safeValue);

      const [result] = await db.execute(
        `INSERT INTO legacy_documents 
        (name, year, grade, semester, teacher, subject, course_code, type, description, file_path, created_by_stu_id, created_by_real_name)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        values
      );

      const documentId = result.insertId;

      if (tags) {
        const tagArray = Array.isArray(tags) ? tags : [tags];
        for (const tagId of tagArray) {
          await db.execute(
            'INSERT INTO legacy_document_tags (document_id, tag_id) VALUES (?, ?)',
            [documentId, tagId]
          );
        }
      }

      return res.json({ success: true, id: documentId });
    } catch (error) {
      console.error(error);
      return res.status(500).json({ success: false, message: '系統錯誤', error: error.message });
    }
  });
};

module.exports = { legacy_upload };