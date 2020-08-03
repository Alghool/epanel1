class Connector {

  constructor(_epanel, _url, _tokenName, _tokenValue){
    if (!!Connector.instance) {
      return Connector.instance;
    }
    Connector.instance = this;

    this.epanel = _epanel;
    this.url = _url;
    this.tokenName = _tokenName;
    this.tokenValue = _tokenValue;
    this.actionUrl = this.url + 'epanel/action/';


    return this;
  }

  static  getConnector(){
    if (!!Connector.instance) {
      return Connector.instance;
    }
    return null;
  }

  sendCoreAction(_action, _data, cb){
    const data = {
      type: 'core',
      action : _action,
      ..._data
    };
    this.send(data, null, false, cb);
  }

  getPanelContent(_action, _data, _type, _permission,  cb){
        const epanel = Epanel.getEpanel();
        const data = {
            type: _type,
            action : _action,
            area: (!!epanel.thisArea)?epanel.thisArea.id: 0,
            permission: _permission ,
            ..._data
        };
        this.send(data, null, false, cb);
    }

  sendAction(_action, _data, _type, _permission,  cb){
    const epanel = Epanel.getEpanel();
    const data = {
      type: _type,
      action : _action,
      area: (!!epanel.thisArea)?epanel.thisArea.id: 0,
      permission: _permission,
      ..._data
    };
    this.send(data, null, false, cb);
  }


  sendFormData(_action, _data, _type, _permission, cb){
    const epanel = Epanel.getEpanel();

      _data.append("type", _type);
      _data.append("action", _action);
      _data.append("permission", _permission);
      _data.append("area", (!!epanel.thisArea)?epanel.thisArea.id: 0);

    this.send(_data, null, true, cb);
  }

  send(_data, _actionUrl, isFormdata, cb){
    const actionUrl = (_actionUrl)? _actionUrl: this.actionUrl;
    let data = _data;
    let ajaxProp = {};
    if(isFormdata){
      data.append(this.tokenName, this.tokenValue);
      data.append('userID', this.epanel.user.id);
      ajaxProp = {
          type: "POST",
          url: actionUrl,
          dataType:'json',
          contentType : false,
          processData : false,
          data: data
      }
    }else{
        data[this.tokenName] = this.tokenValue;
        data.userID = this.epanel.user.id;
        ajaxProp = {
            type: "POST",
            url: actionUrl,
            dataType:'json',
            data: data
        }
    }
    $.ajax(ajaxProp)
    //todo: add handlers for 400, 404, 300 and 500 errors
      .done((data) => {
        this.tokenName = data.security.tokenName;
        this.tokenValue = data.security.tokenValue;
        if(data.success){
          if(data.hasPage) data.hasPage = data.pageStr;
          cb(true,data.data,data.hasPage);
        }else{
          cb(false);
        }
        if(data.msgs){
          epanel.showMsgs(data.msgs);
        }

      })
      .fail((e) => {
        cb(e, 'fail');
      });
  }

}