class User {

  constructor(_url, _userInfo) {
    this.id = _userInfo.user_id,
    this.name = _userInfo.name;
    this.username = _userInfo.username;
    this.email = _userInfo.email;
    this.phone = _userInfo.phone;
    this.role = _userInfo.role_id;
    this.roleType = _userInfo.type;
    this.setting = _userInfo.setting;
    this.gender = _userInfo.gender;
    this.getImagePath(_url, _userInfo.pic);
  }

  getImagePath(_url, _pic){
    if(_pic){
      this.pic = _url + 'image/' + _pic;
    }else{
      this.pic = _url + 'src/epanel/img/user-' + this.gender + '.png';
    }
  }

}