const jwt = require('jsonwebtoken');

function generateRandomString(length = 12) {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  let result = '';

  for (let i = 0; i < length; i++) {
    const randomIndex = Math.floor(Math.random() * chars.length);
    result += chars[randomIndex];
  }

  return result;
}

function _parse_jwt_token(token) {
  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    req.user = decoded;  // 存到 req.user，後面就可以用
    return req.user;
  } catch (err) {
    return {userId: 'None'};
  }
}

function parse_jwt_token(req) {
  const authHeader = req.headers['authorization'];
  if (!authHeader) return {userId: 'None'};
  const token = authHeader.split(' ')[1];
  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    req.user = decoded;  // 存到 req.user，後面就可以用
    return req.user;
  } catch (err) {
    return {userId: 'None'};
  }
}

module.exports = {
    generateRandomString,
    parse_jwt_token,
    _parse_jwt_token
}