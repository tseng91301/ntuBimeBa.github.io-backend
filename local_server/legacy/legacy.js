const { db } = require('../utils/mysql_entry');
const { legacy_upload } = require('./legacy-upload');
const { check_access, _check_access } = require('./access');

const legacy_entry = async (req, res) => {
    if (req.query.check_access) check_access(req, res);
    else if (req.query.list_tags) list_all_tags(req, res);
    else if (req.query.list_documents) list_documents(req, res);
    else if (req.query.insert_tag) insert_tag(req, res);
    else if (req.query.legacy_upload) legacy_upload(req, res);
    else res.status(404).json({ status: 1, error: 'Params not found' });
};

const list_all_tags = async (req, res) => {
    const have_access = await _check_access(req);
    console.log(have_access);
    if((await _check_access(req)) !== 0) {
      return res.status(401).json({status: 2, error: "Access denined"});
    } else {
      console.log("list_all_tags");
      const [rows] = await db.query("SELECT * FROM legacy_tags");
      res.status(200).json(rows);
    }
};

const insert_tag = async (req, res) => {
  if((await _check_access(req)) !== 0) {
    return res.status(401).json({status: 2, error: "Access denined"});
  } else {
    const name = (req.body.name || '').trim();

    if (!name) {
      return res.json({ success: false, message: 'Tag 名稱不可為空' });
    }

    try {
      try {
        // 嘗試插入
        const [result] = await db.execute(
          'INSERT INTO legacy_tags (name) VALUES (?)',
          [name]
        );
        const newId = result.insertId;

        res.json({ success: true, id: newId, name });
      } catch (err) {
        // Duplicate entry (MySQL error code 1062)
        if (err.code === 'ER_DUP_ENTRY') {
          const [rows] = await db.execute(
            'SELECT id FROM legacy_tags WHERE name = ?',
            [name]
          );
          const existingId = rows[0]?.id || null;
          res.json({ success: true, id: existingId, name });
        } else {
          res.json({ success: false, message: err.message });
        }
      }
    } catch (err) {
      res.json({ success: false, message: '資料庫連線失敗' });
    }
  }
};

const list_documents = async (req, res) => {
  const have_access = await _check_access(req);
  console.log(have_access);
  if(have_access !== 0) {
    return res.status(401).json({status: 2, error: "Access denined"});
  } else {
    console.log("list_documents");
    const { tags, subject, grade, year, sortBy, type } = req.query;
    let query = `
      SELECT d.*, GROUP_CONCAT(t.name) AS tags
      FROM legacy_documents d
      LEFT JOIN legacy_document_tags dt ON d.id = dt.document_id
      LEFT JOIN legacy_tags t ON dt.tag_id = t.id
    `;
    const whereClauses = [];
    const params = [];

    if (subject) {
      whereClauses.push("d.subject LIKE ?");
      params.push(`%${subject}%`);
    }
    if (grade) {
      whereClauses.push("d.grade = ?");
      params.push(grade);
    }
    if (year) {
      whereClauses.push("d.year = ?");
      params.push(year);
    }

    if (type) {
      whereClauses.push("d.type = ?");
      params.push(type);
    }

    if (tags) {
      const tagList = tags.split(",");
      whereClauses.push(
        `d.id IN (
          SELECT document_id FROM legacy_document_tags dt2
          JOIN legacy_tags t2 ON dt2.tag_id = t2.id
          WHERE t2.name IN (${tagList.map(() => "?").join(",")})
          GROUP BY document_id
          HAVING COUNT(DISTINCT t2.name) = ?
        )`
      );
      params.push(...tagList, tagList.length);
    }

    if (whereClauses.length > 0) {
      query += " WHERE " + whereClauses.join(" AND ");
    }

    query += " GROUP BY d.id";

    // 排序處理
    const sortMap = {
      created_at_desc: "d.created_at DESC",
      created_at_asc: "d.created_at ASC",
      year_desc: "d.year DESC",
      year_asc: "d.year ASC",
    };
    query += ` ORDER BY ${sortMap[sortBy] || sortMap.created_at_desc}`;

    const [rows] = await db.query(query, params);
    res.json(rows);
  }
  
};

module.exports = {
    legacy_entry,
    _check_access
}
