const { db } = require('../utils/mysql_entry');
const { download_file } = require('./download');

const document_entry = async (req, res) => {
    if (req.query.get_doc) download_file(req, res);
    else if (req.query.list) list(req, res);
    else return res.status(400).json({ success: false, message: '未指定動作' });
}

const list = async (req, res) => {
    try {
        // 1. 查詢申請作業
        const [documents] = await db.execute(
            `SELECT id, name, description, create_date
             FROM open_files`
        );
        res.json({
            success: true,
            documents: documents
        });
    } catch (err) {
        console.error(err);
        res.status(500).json({ success: false, message: '伺服器錯誤' });
    }
}

module.exports = { document_entry };