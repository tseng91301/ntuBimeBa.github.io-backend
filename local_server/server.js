const express = require('express');
const app = express();
const bodyParser = require('body-parser');
const { createClient } = require('redis');

// Custom tools
const { generateRandomString } = require('./utils/basic_tools')

const { db } = require('./utils/mysql_entry')

// Account and authentication
const jwt = require('jsonwebtoken');
const axios = require('axios');
const qs = require('qs');
const { user_entry_get, get_user_information, post_user_information } = require('./users/profile');

// 系產
const { legacy_entry } = require('./legacy/legacy');

// 文件下載
const { document_entry } = require("./documents/documents");

// 申請作業資訊回傳
const { application_entry } = require("./application/application");

// 全域常數初始化
// 請改成你的 LINE App 設定
const LINE_LOGIN_REDIRECT_URI = process.env.LINE_LOGIN_REDIRECT_URI;
const LINE_LOGIN_ID = process.env.LINE_LOGIN_ID;
const LINE_LOGIN_SECRET = process.env.LINE_LOGIN_SECRET;
const LINE_LOGIN_EXPECTED_STATE = process.env.LINE_LOGIN_EXPECTED_STATE;

// CORS 相關設定
const cors = require('cors');
const allowedOrigins = [
  'http://localhost:8081',
  'http://127.0.0.1:8080',
  'https://ntubimeba.github.io',
  'https://frontend-test.ntubimeba.dpdns.org',
];
const corsOptions = {
  origin: function (origin, callback) {
    // 當 origin 為 undefined（例如 curl 或直接在後端呼叫）時也允許
    if (!origin || allowedOrigins.includes(origin)) {
      callback(null, true);
    } else {
      // callback(new Error('Not allowed by CORS'));
    }
  },
  methods: ['GET', 'POST', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization'],
  credentials: true
};
app.use(cors(corsOptions));

const port = 3274;

// 中介軟體，解析 JSON 請求
// app.use(express.json());
app.use(bodyParser.json()); // 支援 JSON
app.use(bodyParser.urlencoded({ extended: true })); // 支援 application/x-www-form-urlencoded

app.get('/login_callback', async (req, res) => {
  const code = req.query.code;
  const state = req.query.state;

  if (state !== LINE_LOGIN_EXPECTED_STATE) {
    return res.status(400).send('bad request');
  }

  try {
    // 交換 access token
    const tokenResponse = await axios.post('https://api.line.me/oauth2/v2.1/token',
      qs.stringify({
        grant_type: 'authorization_code',
        code: code,
        redirect_uri: LINE_LOGIN_REDIRECT_URI,
        client_id: LINE_LOGIN_ID,
        client_secret: LINE_LOGIN_SECRET
      }),
      {
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        }
      }
    );

    const tokenData = tokenResponse.data;
    const accessToken = tokenData.access_token;

    // 取得用戶資料
    const userInfoResponse = await axios.get('https://api.line.me/v2/profile', {
      headers: {
        'Authorization': `Bearer ${accessToken}`
      }
    });


    // 取得並處理資料
    const userInfo = userInfoResponse.data;
    userId = userInfo['userId'];
    // MySQL 查詢 userId
    const [rows] = await db.query('SELECT * FROM bime_linebot_users WHERE uid = ?', [userId]);
    var status_code = null;
    if (rows.length === 0) {
      // 沒有此用戶 → 新增
      await db.query('INSERT INTO bime_linebot_users (uid, username, profile_img, status_code) VALUES (?, ?, ?, ?)', [userId, userInfo['displayName'], userInfo['pictureUrl'], 2]);
      status_code = 2;
      console.log('新增新用戶:', userId);
    } else {
      status_code = rows[0].status_code;
      console.log('用戶已存在:', userId);
    }

    // 簽發 jwt token
    const jwtToken = jwt.sign({ userId }, process.env.JWT_SECRET, { expiresIn: '2h' });

    // Redirect 回前端
    const redirectUrl = `${process.env.FRONTEND_URL}/#/line-login?token=${encodeURIComponent(jwtToken)}&status_code=${status_code}`;
    res.status(200).send(`<script>window.location.href="${redirectUrl}"</script>`);
  } catch (error) {
    console.error('Error:', error.response?.data || error.message);
    res.status(500).send('Internal Server Error');
  }
});

app.get('/user_entry', user_entry_get); // 登入後檢查帳號的完整性
app.get('/user_information', get_user_information);
app.post('/user_information', post_user_information);

// 系產請求部分
app.get('/api/legacy', legacy_entry);
app.post('/api/legacy', legacy_entry);

// 文件下載請求部分
app.get('/api/documents', document_entry);

// 申請作業請求部分
app.get('/api/applications', application_entry);
app.post('/api/applications', application_entry);

// 假設的城市數據
let cities = [
    { id: 1, name: 'Taipei', population: 2717000 },
    { id: 2, name: 'Tokyo', population: 13960000 },
    { id: 3, name: 'New York', population: 8419600 },
];

// 獲取所有城市
app.get('/cities', (req, res) => {
    console.log("Get cities");
    res.json(cities);
});

// 根據 ID 獲取城市
app.get('/cities/:id', (req, res) => {
    const city = cities.find(c => c.id === parseInt(req.params.id));
    if (!city) return res.status(404).send('City not found');
    res.json(city);
});

// 添加新城市
app.post('/cities', (req, res) => {
    const newCity = {
        id: cities.length + 1,
        name: req.body.name,
        population: req.body.population,
    };
    cities.push(newCity);
    res.status(201).json(newCity);
});

app.post('/time_reservation', (req, res) => {
    const newReservation = {
        id: generateRandomString(10),
        name: req.body.name,
        contact: req.body.contact,
        date: req.body.date,
        message: req.body.message,
        other: req.body.other
    }
    console.log(newReservation);
    (async () => {
      const redis = createClient(); // 預設連 localhost:6379
      await redis.connect();

      await redis.publish('newReservation', JSON.stringify(newReservation));

      console.log('✅ 已儲存到 Redis');

      await redis.disconnect();
    })();
    res.status(201).json({"id": newReservation.id});
})

// 啟動伺服器
app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}`);
});
