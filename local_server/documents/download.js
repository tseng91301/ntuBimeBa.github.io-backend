const { db } = require('../utils/mysql_entry');
const path = require('path');

const download_file = async (req, res) => {
    const fileId = req.query.id;
    if (!fileId) return res.status(400).json({ success: false, message: '未指定檔案 ID' });

    const [file] = await db.execute(
        `SELECT id, name, description, path
            FROM open_files
            WHERE id = ?`,
        [fileId]
    );
    if (file.length === 0) {
        return res.status(404).json({ success: false, message: '找不到檔案' });
    }

    // 指定下載檔名，例如：下載下來的名稱跟資料庫 name 一樣
    const downloadName = file[0].name;

    res.download(path.join(process.env.DOCUMENT_STORE_PLACE, file[0].path), downloadName, (err) => {
        if (err) {
            console.error('Download failed: ', err);
            res.status(500).json({ success: false, message: `下載失敗: 未找到檔案` })
        }
    })
}

module.exports = { download_file };