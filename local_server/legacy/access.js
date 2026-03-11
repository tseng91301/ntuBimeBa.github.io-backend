const { find_user_in_db } = require("../users/profile");
const { parse_jwt_token } = require('../utils/basic_tools');

const _check_access = async (req) => {
    console.log("check access");
    // 從 jwt 取得 userId 並檢查
    const user = parse_jwt_token(req);
    if (user.userId == 'None') {
        return 1;
    } else {
        const userData = await find_user_in_db(user.userId);
        if(userData.sa_fee === 0) return 2;
        return 0;
    }
}

const check_access = async (req, res) => {
    // 確認使用者是否有存取系產的權限
    const have_access = await _check_access(req);
    if(have_access === 0) {
        res.status(200).json({ status: 0, message: 'access granted'});
    } else if (have_access === 1) {
        res.status(401).json({ status: 1, error: 'Invalid jwt token' });
    } else if (have_access === 2) {
        res.status(401).json({ status: 2, error: 'Not eligible for legacy' });
    }
}

module.exports = {
    check_access,
    _check_access
}