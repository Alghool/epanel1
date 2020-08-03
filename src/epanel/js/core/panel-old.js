class Panel{

  constructor(_id, _link, _html, _title, _data, _type= 'core',_permission = 0, _parent = null, _setting = {}) {
    this.ready = false;
    this.link = _link;
    this.html = _html;
    this.title = _title;
    this.id = (!!_id)? _id : parseInt(Math.random() * 1000);
    this.data = _data;
    this.type = _type;
    this.parent = _parent;
    this.permisson = (_permission == 0 && _type == 'permission') ? Epanel.getEpanel().thisPermission.id: _permission;
    this.setting = {};

    this.setting = $.extend({}, this.setting, _setting);

    if (!this.link && !this.html){
      this.ready = true;
      return;
    }

    this.getHtmlFromServer(()=>{
      this.ready = true;
    });

  }


  draw(cb, parseOnly = false){
    if(this.ready === false){
      setTimeout(this.draw.bind(this), 400,cb, parseOnly);
      return;
    }

    if(!!this.error){
      cb();
      return;
    }

    const template = $('#epanel-panel-block').clone();
    template.find('.panel-title-js').text( i18next.t(this.title));
    template.find('.root-element-js').prop('id', 'panel-'+this.id);
    template.find('.panel-body-js').html(this.html);
    template.find('.panel-toolbar-js [data-action="collapse"]').on("click", this.onCollapse.bind(this));
    template.find('.panel-toolbar-js [data-action="close"]').on("click", this.onClose.bind(this));
    if(!this.link){
      template.find('.panel-toolbar-js .reload').remove();
    }else{
      template.find('.panel-toolbar-js [data-action="reload"]').on("click", this.onRefresh.bind(this));
    }
    const actionHandler = this.actionHandler.bind(this);
    template.find('[action]').each(function (i) {
      event = $(this).attr('event')? $(this).attr('event') : 'click';
      $(this).on(event, actionHandler)
    });

    if(parseOnly){
      cb(template.find('.root-element-js'));
    }else{
      $('#panels-holder').append(template.find('.root-element-js'));
      cb();
    }

  }

  redraw(_parent, cb){
    this.draw((_html)=>{
      $('#panels-holder').insertBefore(_html, _parent);
      _parent.remove();
      cb();
    });
  }

  getHtmlFromServer(cb){
    if(!this.link) {
      cb();
      return;
    }

    this.connector = Connector.getConnector();
    this.connector.getPanelContent(this.link,this.data, this.type, this.permisson, (_success, _data, _hasPage)=>{
      if(_success && !!_hasPage){
        this.localizeHtml($(_hasPage), (_result) =>{
          this.serverResponse = _result;
          //see if panel has own title
          this.title = (_result.find('.panel-title').data('title') != '')?_result.find('.panel-title').data('title'): this.title;
          if(this.setting.mergeHtml) {
            this.html =  this.serverResponse + this.html;
          }else{
            this.html =  this.serverResponse ;
          }
          cb();
        });
      }else{
        this.error = {
          'error': 'error',
          'msg': (_hasPage)? 'no page for this panel action': 'connection error',
          'data': _data
        };
        if(window.debug) console.log(this.error);
        cb();
      }
    })
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


  //  panel default action////////////////////////////////////////////////////////////////////////////////
  onCollapse() {
    const me = $('#panel-'+this.id+'.root-element-js');
    const panelCollapse = me.children(':not(.panel-heading)');
    me.toggleClass('panel-collapsed');

    me.find('[data-action="collapse"]').toggleClass('rotate-180');
    containerHeight(); // recalculate page height
    panelCollapse.slideToggle(150);
  }
  onClose(){
      const $panelClose = $('#panel-'+this.id+'.root-element-js *');
      containerHeight(); // recalculate page height
      $panelClose.slideUp(150, function() {
        $panelClose.remove();
      });
  }
  onRefresh(){
    const epanel = Epanel.getEpanel();
    epanel.showLoader();
    this.ready =false;
    this.getHtmlFromServer(()=>{
      this.ready = true;
      this.redraw($('#panel-'+this.id), ()=>{
        epanel.removeLoader();
      });
    })
  }

  //  action handlers ////////////////////////////////////////////////////////////////////////////////
  actionHandler(e){
    const epanel = Epanel.getEpanel();
    epanel.showLoader();
    const elem = $(e.target).closest('[action]');
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
            this.doAction(elem);
          }else{
            epanel.removeLoader();
          }
        }
      });
    }else{
      this.doAction(elem);
    }
  }

  doAction(elem){
    const actionType = elem.attr('action');
    const connector = Connector.getConnector();
    const link = elem.attr('link');
    const type = elem.attr('actionType')? elem.attr('type'): this.type;
    const data = !!elem.data()? elem.data() :{};
    const title = elem.attr('title');
    const permission = elem.attr('permission')? elem.attr('permission'): this.permisson;

    switch (actionType){
      case 'permission':
        epanel.permissions[elem.attr('permission')].onClick();
        break;
      case 'ajax' :
        connector.sendAction(link, data, type, permission, (_success, _data, _hasPag)=>{
          this.afterAction(elem, _data, _hasPag, ()=>{
            epanel.removeLoader();
          });
        });
        break;
      case 'action':
        connector.sendAction(link, data, type, permission, (_success, _data, _hasPag)=>{
          this.afterAction(elem, _data, _hasPag, ()=>{
            epanel.removeLoader();
          });
        });
        break;
      case 'form':
        const formID = elem.attr('form-id');
        const myForm = document.getElementById(formID);
        const formdata = new FormData(myForm);
        const hasValidate = elem.attr('hasValidate');
        if(!!hasValidate){
          const options = elem.attr('hasValidateOptions');
          const validator = $('#'+formID).parsley((!!options)? options(): {});
          if(!validator.validate()){
            epanel.removeLoader();
            return;
          }
        }
        connector.sendFormData(elem.attr('link'), formdata, this.type, permission, (_success, _data, _hasPage)=>{
          if(_success){
            this.afterAction(elem, _data, _hasPage, ()=>{
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
        const panelID = elem.attr('panel-id');
        let actionPanel = new Panel(panelID, link, '', title, data, type, permission, this);
        epanel.addToPage([actionPanel], ()=>{
          this.afterAction(elem, {}, true, ()=>{
            epanel.removeLoader();
          });
        });
        break;
    }
  }

  afterAction(_elem, _data, _hasPage, cb){
      const onResult = _elem.attr('onResult');
      //todo: handle all result types
        switch(onResult){
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
          case 'replaceDiv':
            if(_hasPage){
              //todo: clean this
              const div = _elem.attr('div');
              const content = $(_hasPage);
              const actionHandler = this.actionHandler.bind(this);
              content.find('[action]').each(function (i) {
                event = $(this).attr('event')? $(this).attr('event') : 'click';
                $(this).on(event, actionHandler)
              });
              this.localizeHtml(content, (_content)=>{
                $('#panel-'+this.id +' '+div).html(_content);
              })
            }
            cb();
            break;
          default:
            cb();
        }
    }
}