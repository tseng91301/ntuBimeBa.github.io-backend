// routes/legacy.js
const express = require('express');
const router = express.Router();
const db = require('../db');  // pg 連線物件

router.get('/folders', async (req, res) => {
  try {
    const result = await db.query('SELECT * FROM folders ORDER BY created_at DESC');
    res.json(result.rows);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: '資料庫錯誤' });
  }
});

module.exports = router;
