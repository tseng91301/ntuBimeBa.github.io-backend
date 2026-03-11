const { parse_jwt_token } = require('../utils/basic_tools');
const { db } = require('../utils/mysql_entry');

const find_user_in_db = async (userId) => {
    const [rows] = await db.query('SELECT * FROM bime_linebot_users WHERE uid = ?', [userId]);
    if(rows.length === 0) {
        return {userId: 'None'};
    } else {
        return rows[0];
    }
}

const find_user_by_token = async (token) => {
  return await find_user_by_token(parse_jwt_token(token).userId);
}

const get_user_information = async (req, res) => {
  // 從 jwt 取得 userId 並檢查
  const user = parse_jwt_token(req);
  if (user.userId == 'None') {
    return res.status(401).json({ status: 1, error: 'Invalid jwt token.' });
  }

  res.status(200).json(await find_user_in_db(user.userId));
}

const post_user_information = async (req, res) => {
  const user = parse_jwt_token(req);
  if (user.userId == 'None') {
    return res.status(401).json({ status: 1, error: 'Invalid jwt token.' });
  }

  const { real_name, stu_id, email, address, tel, discord } = req.body;

  if (!real_name || !stu_id) {
    return res.status(400).json({ error: '真實姓名與學號為必填欄位' });
  }

  try {
    const sql = `
      UPDATE bime_linebot_users
      SET real_name = ?, stu_id = ?, email = ?, address = ?, tel = ?, discord = ?, status_code = ?
      WHERE uid = ?
    `;

    const [result] = await db.query(sql, [
      real_name,
      stu_id,
      email || null,
      address || null,
      tel || null,
      discord || null,
      1,
      user.userId,
    ]);

    return res.json({ success: true, affectedRows: result.affectedRows });
  } catch (err) {
    console.error(err);
    return res.status(500).json({ error: '資料庫錯誤' });
  }
}

const user_entry_get = async (req, res) => {
  const user = parse_jwt_token(req);
  if (user.userId == 'None') {
    console.log("401");
    return res.status(401).json({ status: 1, error: 'Invalid jwt token.' });
  }

  const userData = await find_user_in_db(user.userId);
  if (userData.userId == 'None') {
    console.log("404");
    return res.status(404).json({ status: 2, error: 'User not found.' });
  }

  if (!userData.stu_id || !userData.real_name) {
    console.log("400");
    return res.status(400).json({ status: 3, error: 'Missing student id or real name.' });
  }
  
  res.status(200).json({ status: 0, message: 'User verified!' });
};

module.exports = {
    user_entry_get,
    get_user_information,
    find_user_in_db,
    post_user_information,
    find_user_by_token
}