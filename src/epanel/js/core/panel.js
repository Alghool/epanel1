class Panel{

constructor(_id, _link, _html, _title, _data, _type= 'core',_permission = 0, _parent = null, _setting = {}) {
    this.epanel = Epanel.getEpanel();
    this.myPage = null;
    this.ready = false;
    this.template = null;
    this.link = _link;
    this.html = _html;
    this.title = _title;
    this.id = _id;
    this.data = _data;
    this.type = _type;
    this.parent = _parent;
    this.permisson = (_permission == 0 && _type == 'permission') ? Epanel.getEpanel().thisPermission.id : _permission;
    this.setting = {};
    this.action = [];
    this.children = [];
    this.lastAction = null;

    this.setting = $.extend({}, this.setting, _setting);

    if (!this.link && !this.html){
      this.ready = true;
      return;
    }

    this.updateTemplate();
  }

  setPage(_page){
    this.myPage = _page;
  }

  getTemplate(cb){
    if(this.ready === false){
      setTimeout(this.getTemplate.bind(this), 400,cb);
      return;
    }
    if(!!this.error){
      cb(false);
      return;
    }

    cb(this.template);
  }

  //  panel default action////////////////////////////////////////////////////////////////////////////////
  onCollapse() {
    const panelCollapse = this.template.children(':not(.panel-heading)');
    this.template.toggleClass('panel-collapsed');

    this.template.find('[data-action="collapse"]').toggleClass('rotate-180');
    containerHeight(); // recalculate page height
    panelCollapse.slideToggle(150);
  }

  onClose(){
    const panelClose = this.template;
    containerHeight(); // recalculate page height
    panelClose.slideUp(150, ()=>{
      panelClose.remove();
    });
    if(!!this.myPage) this.myPage.panelClosed(this);
  }

  onRefresh(){
    const epanel = Epanel.getEpanel();
    epanel.showLoader();
    this.lastAction = null;
    this.updateTemplate();
    if(!!this.myPage) this.myPage.panelRefreshed(this);
    this.children.forEach((_panel, _index)=> {
      _panel.onClose();
    });
    epanel.removeLoader();
  }

  //  action handlers ////////////////////////////////////////////////////////////////////////////////
  reloadLastAction(){
    const epanel = Epanel.getEpanel();
    epanel.showLoader();
    if(!!this.lastAction){
      this.doAction(this.lastAction);
      if(!!this.myPage) this.myPage.panelRefreshed(this);
      this.children.forEach((_panel, _index)=> {
        _panel.onClose();
      });
      epanel.removeLoader();
    }else{
      this.onRefresh();
    }
  }

  actionHandler(e){
    const epanel = Epanel.getEpanel();
    epanel.showLoader();
    const elem = e.data.element;
    const msg = elem.attr('actionConfirm');
    if(!!msg){
      $.msgBox({
        title: i18next.t("confirm"),
        content: i18next.t('msg:'+msg),
        type: "confirm",
        imagePath: epanel.mainUrl+"src/epanel/img/",
        buttons: [{ value: i18next.t("OK") }, { value: i18next.t("cancel") }],
        success: (result) =>{
          if (result == i18next.t("OK")) {
            this.getActionParameters(elem);
          }else{
            epanel.removeLoader();
          }
        }
      });
    }else{
      this.getActionParameters(elem);
    }
  }

  getActionParameters(_elem){
    const params = {
      actionType : _elem.attr('action'),
      link : _elem.attr('link'),
      type : _elem.attr('type') && ['permission','area', 'core'].indexOf(_elem.attr('type')) > 0 ?
        _elem.attr('type'): this.type,
      data : !!_elem.data()? _elem.data() :{},
      title : _elem.attr('title'),
      permission : _elem.attr('permission')? _elem.attr('permission'): this.permisson,
      formID : _elem.attr('form-id'),
      hasValidate : _elem.attr('hasValidate'),
      panelID : _elem.attr('panel-id'),
      onResult : _elem.attr('onResult'),
      div : _elem.attr('div')
    };

    this.doAction(params)
  }

  callAction(_param){
      if(!('permission' in _param)){
        _param.permission = this.permisson;
      }
      if(!('type' in _param) || ['permission','area', 'core'].indexOf(_param.type) < 0){
        _param.type = this.type;
      }
      if(!('data' in _param)){
        _param.data = {};
      }
    this.doAction(_param);
  }

  doAction(_params){
    const connector = Connector.getConnector();

    switch (_params.actionType){
      case 'permission':
        const eventSimulator = {target: _params};
        epanel.permissions[_params.permission].onClick(eventSimulator);
        break;
      case 'ajax' :
        this.lastAction = _params;
        connector.sendAction(_params.link, _params.data, _params.type, _params.permission, (_success, _data, _hasPag)=>{
          this.afterAction(_params, _data, _hasPag, ()=>{
            epanel.removeLoader();
          });
        });
        break;
      case 'action':
        connector.sendAction(_params.link, _params.data, _params.type, _params.permission, (_success, _data, _hasPag)=>{
          this.afterAction(_params, _data, _hasPag, ()=>{
            epanel.removeLoader();
          });
        });
        break;
      case 'form':
        const myForm = document.getElementById(_params.formID);
        const formdata = new FormData(myForm);
        if(!!_params.hasValidate){
          const validator = this.template.find('#'+_params.formID).parsley((!!_params.options)? _params.options(): {});
          if(!validator.validate()){
            epanel.removeLoader();
            return;
          }
        }
        connector.sendFormData(_params.link, formdata, _params.type, _params.permission, (_success, _data, _hasPage)=>{
          if(_success){
            this.afterAction(_params, _data, _hasPage, ()=>{
              epanel.removeLoader();
            })
          }else{
            this.error = {
              'error': 'error',
              'msg': 'form submit failed',
              'data': _data
            };
            if(window.debug) console.log(this.error);
            epanel.removeLoader();
          }
        });
        break;
      case 'panel':
        let actionPanel = new Panel(_params.panelID, _params.link, '', _params.title, _params.data, _params.type, _params.permission, this);
        this.children.push(actionPanel);
        epanel.addToPage([actionPanel], ()=>{
          this.afterAction(_params, {}, true, ()=>{
            epanel.removeLoader();
          });
        });
        break;
    }
  }


  afterAction(_params, _data, _hasPage, cb){
    switch(_params.onResult){
      case 'reloadme':
        this.onRefresh();
        break;
      case 'destroyAndReloadParent':
        this.onClose();
        if(!!this.parent) {
          this.parent.onRefresh();
        }
        else{
          cb();
        }
        break;
      case 'destroyAndReloadParentAction':
        this.onClose();
        if(!!this.parent) {
          this.parent.reloadLastAction();
        }
        else{
          cb();
        }
        break;
      case 'replaceDiv':
        if(_hasPage){
          //todo: clean this
          const content = $(_hasPage);

          const actionHandler = this.actionHandler.bind(this);
          content.find('[action]').each(function (i) {
            event = $(this).attr('event')? $(this).attr('event') : 'click';
            $(this).on(event,{element: $(this)}, actionHandler)
          });
          Epanel.getEpanel().localizeHtml(content, (_content)=>{
            this.template.find(_params.div).html(_content);
          })
        }
        cb();
        break;
      case 'custom':
        _params.fun(_data, _hasPage, cb);
        break;
      default:
        cb();
    }
  }

/// internal function  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  getHtmlFromServer(cb){
    if(!this.link) {
      cb(false);
      return;
    }

    this.connector = Connector.getConnector();
    this.connector.getPanelContent(this.link,this.data, this.type, this.permisson, (_success, _data, _hasPage)=>{
      if(_success && !!_hasPage){
          this.serverResponse = _hasPage;
          cb(true);

      }else{
        this.error = {
          'error': 'error',
          'msg': (_hasPage)? 'no page for this panel action': 'connection error',
          'data': _data
        };
        if(window.debug) console.log(this.error);
        cb(false);
      }
    })
  }

  updateTemplate(){
    this.ready = false;
    this.getHtmlFromServer((done)=>{
      if(done){
        if(this.setting.mergeHtml) {
          this.html =  this.serverResponse + this.html;
        }else{
          this.html =  this.serverResponse ;
        }
      }
      this.epanel.localizeHtml($(this.html), (_html)=>{
        this.html = _html;
        //see if panel has own title
        //todo: change this title div class to panel server parameters or something
        this.title = (_html.find('.panel-title').data('title') != '')?_html.find('.panel-title').data('title'): this.title;
        this.link = (_html.find('.panel-title').data('link'))?_html.find('.panel-title').data('link'): this.link;

        const template = $('#epanel-panel-block').clone();
        template.find('.panel-title-js').text( i18next.t(this.title));
        template.find('.root-element-js').prop('id', 'panel-'+this.id);
        template.find('.root-element-js').attr('data-id', this.id);
        template.find('.panel-body-js').html(this.html);
        template.find('.panel-toolbar-js [data-action="collapse"]').on("click", this.onCollapse.bind(this));
        template.find('.panel-toolbar-js [data-action="close"]').on("click", this.onClose.bind(this));
        template.find('.panel-toolbar-js [data-action="reload"]').on("click", this.onRefresh.bind(this));

        const actionHandler = this.actionHandler.bind(this);
        template.find('[action]').each(function (i) {
          event = $(this).attr('event')? $(this).attr('event') : 'click';
          $(this).on(event,{element: $(this)}, actionHandler)
        });

        if(!this.template){
          this.template = template.find('.root-element-js');
        }else{
          this.template.html(template.find('.root-element-js>*'));
        }

        this.ready = true;
      })

    });
  }

}