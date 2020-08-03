class Epanel {


  constructor(_url , _epanelData, cb) {
    if (!!Epanel.instance) {
      return Epanel.instance;
    }
    Epanel.instance = this;

    this.ready = false;
    this.epanelData = JSON.parse(_epanelData);
    this.mainUrl = _url;
    this.rootArea = null;
    this.thisArea = null;
    this.permissions = [];
    this.thisPermission = null;
    this.thisPage = new Page();

    this.stack_context = {
      "dir1": "up",
      "dir2": "left",
      "push":"down"
    };

    this.Localization(this.epanelData.setting.language ==='arabic' ? 'ar': 'en' , ['permissions','areas', 'core', 'b', 'msg', 'notifi'], 'core', this.mainUrl, () => {
      this.user = new User(this.mainUrl, this.epanelData);
      this.connector = new Connector(this, this.mainUrl, this.epanelData.epanelSetting.tokenName, this.epanelData.epanelSetting.tokenValue);
      //build the top left toolbar of epanel according to user policies
      this.buildToolbar(this.epanelData.epanel.policies);
      //build user widget
      this.buildUserWidget(this.user, this.epanelData.epanel.policies);

      //build areas
      this.buildAreasPermissions(this.epanelData.isMultiArea,this.epanelData.epanel, ()=>{
        this.thisArea.draw($('#area-holder'));
        this.thisArea.onClick(null, ()=>{
          this.startBreather();
          this.removeLoader();
          //epanel ready to rock
          this.ready = true;
          if(cb){
            cb(this);
          }
        });
      });
    })
  }

  static getEpanel(){
    if (!!Epanel.instance) {
      return Epanel.instance;
    }
    return null;
  }

  // build templates /////////////////////////////////////////////////////////////////////////////////////////////////////////
  buildToolbar(_polices){
    if("epanelSetting" in _polices && _polices.epanelSetting == 1){
      this.drawToolbarItem('epanelSetting', 'glyphicon-wrench',i18next.t('epanelSetting'));
      $('#epanelSetting').on("click", this.showCorePage.bind(this,'epanelSetting', 'epanel/page/getEpanelSetting', i18next.t('epanelSetting'), this.user.id));
    }

    if("showUsers" in _polices && _polices.showUsers == 1){
      this.drawToolbarItem('showUsers', 'glyphicon-user',i18next.t('userlist'));
      $('#showUsers').on("click", this.showCorePage.bind(this,'showUsers', 'users/User_epanel/getUsers', i18next.t('userlist'), this.user.id));
    }

    if("showRoles" in _polices && _polices.showRoles == 1){
      this.drawToolbarItem('showRoles', 'glyphicon-briefcase',i18next.t('rolelist'));
      $('#showRoles').on("click", this.showCorePage.bind(this,'showRoles', 'epanel/role/getRoles', i18next.t('rolelist'), this.user.id));
    }

    if("showLog" in _polices && _polices.showLog == 1){
      this.drawToolbarItem('showLog', 'glyphicon-th-list',i18next.t('loglist'));
      $('#showLog').on("click", this.showCorePage.bind(this,'showLog', 'epanel/page/showLog', i18next.t('loglist'), this.user.id));
    }

    if("newNotification" in _polices && _polices.newNotification == 1){
      this.drawNotifications('newNotification', 'glyphicon-bell',i18next.t('notification'))
    }
  }

  drawToolbarItem(_id, _icon, _title){
    const template = $('#epanel-templates #epanel-toolbar-block').clone();
    template.find('.root-element-js').prop('id', _id);
    template.find('.i-element-js').addClass(_icon);
    template.find('.i-element-js').prop('title',_title);
    template.find('.title-element-js').text(_title);
    $('#toolbar').append(template.find('.root-element-js'));
  }

  drawNotifications(_id, _icon, _title){
    const template = $('#epanel-templates #epanel-notificationholder-block').clone();
    template.find('.root-element-js').prop('id', _id);
    template.find('.i-element-js').addClass(_icon);
    template.find('.i-element-js').prop('title',_title);
    template.find('.title-element-js').text(_title);
    $('#toolbar').append(template.find('.root-element-js'));
  }

  drawNotification(_notification){
    if($('#notifi-'+_notification.notification_id).length > 0) return;
    const template = $('#epanel-templates #epanel-notification-block').clone();
    template.find('.root-element-js').prop('id', 'notifi-'+_notification.notification_id);

    if(_notification.new == 1){
      template.find('.root-element-js').addClass('new');
      this.drawNotificationCounter(1);
      template.find('.root-element-js').on('click', (e)=>{
        const elem = $(e.target).closest('.root-element-js');
        if(elem.hasClass('new')){
          elem.removeClass('new');
          epanel.drawNotificationCounter(-1);
        }
      });
    }
    const notification  = JSON.parse(_notification.text);
    const text = i18next.t('notifi:' + notification.text, notification.data);
    template.find('.text-element-js').text(text);
    template.find('.user-name-js').text(_notification.userName);
    template.find('.date-js').text(moment.unix(_notification.date).zone("+00:00").format('Y-M-D HH:mm'));
    template.find('.root-element-js').attr('data-date', _notification.date);
    // template.find('.root-element-js').on('click', this.notificationClicked);
    //todo: complete notification clicked function

    $('#notification-holder').prepend(template.find('.root-element-js'));
    if($('#notification-holder .root-element-js').length > this.user.setting['notificationCount']){
      $('#notification-holder .root-element-js:not(.new)').last().remove();
    }
  }

  drawNotificationCounter(count = 0){
    if(count == 0){
      this.notificationCounter = 0;
      $('#notification-counter').addClass('hidden');
    }else{
      this.notificationCounter = (!this.notificationCounter)? count : this.notificationCounter + count;
      if(this.notificationCounter < 1){
        this.notificationCounter = 0;
        $('#notification-counter').addClass('hidden');
      }else{
        $('#notification-counter').removeClass('hidden');
      }
    }
    $('#notification-counter').text(this.notificationCounter);
  }

  buildUserWidget(_user, _polices){
    $('#user-pic').prop('src', _user.pic);
    $('#user-name').text(_user.name);
    $('#user-username').text(_user.username);

    if("getUserSetting" in _polices && _polices.getUserSetting == 1){
      this.drawUserOptionItem('getUserSetting', 'icon-gear',i18next.t('mysetting'));
      $('#getUserSetting').on("click", this.showCorePage.bind(this, 'getUserSetting', 'users/User_epanel/getUserSetting', i18next.t('mysetting'), this.user.id));
    }

    if("getUserProfile" in _polices && _polices.getUserProfile == 1){
      this.drawUserOptionItem('getUserProfile', 'icon-user',i18next.t('myProfile'));
      $('#getUserProfile').on("click", this.showCorePage.bind(this, 'getUserProfile', 'users/User_epanel/getUserProfile', i18next.t('myProfile'), this.user.id));
    }
  }

  drawUserOptionItem(_id, _icon, _title){
    const template = $('#epanel-templates #epanel-useroption-block').clone();
    template.find('.root-element-js').prop('id', _id);
    template.find('.i-element-js').addClass(_icon);
    template.find('.i-element-js').prop('title',_title);
    template.find('.title-element-js').text(_title);
    $('#user-options').prepend(template.find('.root-element-js'));
  }

  buildAreasPermissions(_isMultiArea, _epanelStructure, cb){
    if(_isMultiArea){
      //todo: implement server request to get ids of permissions for each area
    }else{
      let area = new Area(i18next.t('areas:'+_epanelStructure.areas[0].name), _epanelStructure.areas[0], null, null);
      this.rootArea = area;
      this.thisArea = area;

      var rootPermission = {};
      _epanelStructure.permissions.forEach((_item, _index)=>{
        let permission = null;
        if(_item.lvl == 0){
          permission = new Permission(i18next.t('permissions:'+_item.name), _item, null, null, area);
          rootPermission[_item.permission_id] = permission;
        }else{
          let parentPermission = this.permissions[_item.parent];
          permission = new Permission(i18next.t('permissions:'+_item.name), _item, parentPermission, null, area);
          parentPermission.addChild(permission);
        }
        this.permissions[permission.id] = permission;
        if(_index + 1 == _epanelStructure.permissions.length && cb){
          cb();
        }
      });
    }
  }

  // actions functions //////////////////////////////////////////////////////////////////////////////////////////////////////////
  showLoader(){
    $('#loading-panel').removeClass('hidden');
  }

  removeLoader(){
    $('#loading-panel').addClass('hidden');
  }

  startBreather(){
    let breathFailCounter = 0;
    const interval = this.epanelData.epanelSetting.breathInterval * 1000;
    this.breather = setInterval(()=>{
      const lastTime = $('#notification-holder li').length > 0 ? $('#notification-holder li').first().attr('data-date') : 0;
      this.connector.sendCoreAction('epanel/_breath', {'lastTime':lastTime}, (_success, _data)=>{
          if(_success){
            breathFailCounter = 0;
            $('#server-clock').text(_data.time);
            if(_data.notifications){
              $('#notification-holder #nonotifi').remove();
              _data.notifications.forEach((_item, _index)=>{
                this.drawNotification(_item);
                const notification = JSON.parse(_item.text);
                this.showMsgs([{'type':'info','msg':notification.text, 'data':notification.data}], 'notifi')
              })
            }

          }else{
            if( ++breathFailCounter > 4){
              clearInterval(this.breather); // stop the interval
              $('#server-clock').remove();
            }
          }
      })
    },interval)
  }

  newPage(_title,_panels, cb = null){
    this.thisPage = new Page(_title);

    if(!!_panels){
      this.thisPage.addPanels(_panels, ()=>{
        if(!!cb){
          cb(true);
        }else{
          this.removeLoader();
        }
      })
    }else{
      if(!!cb){
        cb(true);
      }else{
        this.removeLoader();
      }
    }

  }

  addToPage(_panels, cb = null){
    if(!!_panels){
      this.thisPage.addPanels(_panels, ()=>{
        if(!!cb){
          cb(true);
        }else{
          this.removeLoader();
        }
      })
    }else{
      if(!!cb){
        cb(true);
      }else{
        this.removeLoader();
      }
    }
  }
  // core pages functions //////////////////////////////////////////////////////////////////////////////////////////////////////////
  showCorePage(_panelID, _link, _title, _id){
    this.showLoader();
    //create my panel
    let corePanel = new Panel(_panelID, _link, '', _title, {id:_id}, 'core');
    //clear panels
    this.newPage(_title, [corePanel], ()=>{
      this.removeLoader();
    });
  }

  // helper functions //////////////////////////////////////////////////////////////////////////////////////////////////////////
  Localization(_lang, _ns, _defaultNS, _baseURL, cb){

    const ns = (Array.isArray(_ns))? _ns.concat(['common']): ['common'];
    const defaultNS = (typeof _defaultNS !== 'undefined')? _defaultNS : 'common';
    const lang = (typeof _lang !== 'undefined')? _lang : 'en';
    const baseURL =  (typeof _baseURL !== 'undefined')? _baseURL: '';

    i18next
      .use(i18nextXHRBackend)
      .init({
        lng: lang,
        debug: false,
        load: ['ar', 'en'],
        fallbackLng: ["en"],
        ns: ns,
        defaultNS: _defaultNS,
        backend: {
          // todo: get base url from main function
          loadPath: baseURL +'src/epanel/locales/{{lng}}/{{ns}}.json'
        }

      }, function (err, t) {
        // initialized and ready to go!
        $('lang, [localize]').each(function () {
          const key = $(this).text().trim();
          $(this).text(i18next.t(key))
        });
        $('[localize-holder]').each(function(){
          const key = $(this).attr('placeholder').trim();
          $(this).attr('placeholder', i18next.t(key));
        });
        $('[localize-title]').each(function(){
          const key = $(this).attr('title').trim();
          $(this).attr('title', i18next.t(key));
        });
        if(cb){
          cb();
        }
      });
  }

  localizeHtml(_html, cb){
    _html.find('lang, [localize]').each(function () {
      const key = $(this).text().trim();
      $(this).text(i18next.t(key))
    });
    _html.find('[localize-holder]').each(function(){
      const key = $(this).attr('placeholder').trim();
      $(this).attr('placeholder', i18next.t(key));
    });
    _html.find('[localize-title]').each(function(){
      const key = $(this).attr('title').trim();
      $(this).attr('title', i18next.t(key));
    });

    cb(_html);
  }

  showMsgs(_msgs, _textKey = 'msg'){
    _msgs.forEach((msg, index)=>{
      const text = i18next.t(_textKey +':' + msg.msg, msg.data);
      new PNotify({
        type:  msg.type,
        stack: this.stack_context,
        text: text
      });
    });
  }

  // getter and setter /////////////////////////////////////////////////////////////////////////////////////////////////////////
  getUserData(){
    return this.epanelData;
  }
}