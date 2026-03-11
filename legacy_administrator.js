// routes/legacy.js
const express = require('express');
const router = express.Router();
const pool = require('../db');
const { verifyToken } = require('../middleware/auth');
const multer = require('multer');
const upload = multer({ dest: 'uploads/' });

//  GET /api/legacy?check_access=true
router.get('/', verifyToken, async (req, res) => {
  if (req.query.check_access) {
    return res.status(200).json({ message: 'Access granted' });
  }

  if (req.query.list_tags) {
    try {
      const { rows } = await pool.query('SELECT * FROM tags');
      return res.json(rows);
    } catch (err) {
      return res.status(500).json({ error: 'Failed to fetch tags' });
    }
  }

  if (req.query.list_documents) {
    const { subject, grade, year, type, tags, sortBy } = req.query;
    const values = [];
    let sql = `SELECT d.*, array_agg(t.name) AS tags
               FROM documents d
               LEFT JOIN document_tags dt ON d.id = dt.document_id
               LEFT JOIN tags t ON dt.tag_id = t.id
               WHERE 1=1`;

    if (subject) {
      sql += ' AND d.subject ILIKE $' + (values.length + 1);
      values.push(`%${subject}%`);
    }
    if (grade) {
      sql += ' AND d.grade = $' + (values.length + 1);
      values.push(grade);
    }
    if (year) {
      sql += ' AND d.year = $' + (values.length + 1);
      values.push(year);
    }
    if (type) {
      sql += ' AND d.type = $' + (values.length + 1);
      values.push(type);
    }

    sql += ' GROUP BY d.id';

    if (sortBy === 'created_at_desc') sql += ' ORDER BY d.created_at DESC';
    if (sortBy === 'created_at_asc') sql += ' ORDER BY d.created_at ASC';
    if (sortBy === 'year_desc') sql += ' ORDER BY d.year DESC';
    if (sortBy === 'year_asc') sql += ' ORDER BY d.year ASC';

    try {
      const { rows } = await pool.query(sql, values);
      return res.json(rows);
    } catch (err) {
      console.error(err);
      return res.status(500).json({ error: 'Failed to fetch documents' });
    }
  }

  res.status(400).json({ error: 'Invalid query' });
});

//  POST /api/legacy/upload（僅供維護者使用，不外流）
router.post('/upload', verifyToken, upload.single('file'), async (req, res) => {
  const { originalname, path } = req.file;
  const { name, subject, teacher, year, grade, semester, type, tags } = req.body;

  try {
    const insertDocQuery = `
      INSERT INTO documents (name, subject, teacher, year, grade, semester, type, created_at)
      VALUES ($1, $2, $3, $4, $5, $6, $7, NOW()) RETURNING id
    `;
    const docValues = [name || originalname, subject, teacher, year, grade, semester, type];
    const { rows } = await pool.query(insertDocQuery, docValues);
    const docId = rows[0].id;

    const tagList = tags.split(',').map(t => t.trim());
    for (const tag of tagList) {
      const tagRes = await pool.query('INSERT INTO tags (name) VALUES ($1) ON CONFLICT (name) DO UPDATE SET name=EXCLUDED.name RETURNING id', [tag]);
      const tagId = tagRes.rows[0].id;
      await pool.query('INSERT INTO document_tags (document_id, tag_id) VALUES ($1, $2)', [docId, tagId]);
    }

    res.status(201).json({ message: 'File uploaded and tagged successfully (staff only)' });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Upload failed' });
  }
});

//  PUT /api/legacy/tags/:documentId 更新某檔案的標籤
router.put('/tags/:documentId', verifyToken, async (req, res) => {
  const { documentId } = req.params;
  const { tags } = req.body;

  try {
    await pool.query('DELETE FROM document_tags WHERE document_id = $1', [documentId]);
    const tagList = tags.split(',').map(t => t.trim());

    for (const tag of tagList) {
      const tagRes = await pool.query('INSERT INTO tags (name) VALUES ($1) ON CONFLICT (name) DO UPDATE SET name=EXCLUDED.name RETURNING id', [tag]);
      const tagId = tagRes.rows[0].id;
      await pool.query('INSERT INTO document_tags (document_id, tag_id) VALUES ($1, $2)', [documentId, tagId]);
    }

    res.status(200).json({ message: 'Tags updated' });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Failed to update tags' });
  }
});

//  DELETE /api/legacy/:documentId 下架檔案
router.delete('/:documentId', verifyToken, async (req, res) => {
  const { documentId } = req.params;

  try {
    await pool.query('DELETE FROM document_tags WHERE document_id = $1', [documentId]);
    await pool.query('DELETE FROM documents WHERE id = $1', [documentId]);
    res.status(200).json({ message: 'Document deleted' });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Failed to delete document' });
  }
});

module.exports = router;
