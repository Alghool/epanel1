class Area{

  constructor(_title, _area, _parent, _children,  _permissions = null){
    this.id = _area.id;
    this.name = _area.name;
    this.display = _area.display;
    this.lvl = _area.lvl;
    this.link = _area.home_page;
    this.icon = _area.icon;
    this.title = _title;
    this.isroot = _parent === null;
    this.parent = _parent;
    this.children = _children;
    this.permissions = _permissions;

    if(this.permissions){
      this.updatePermissionsArea();
    }
  }

  draw(parentDev){
    if(this.display === 'none') return;

    const template = $('#epanel-templates #epanel-area-'+this.display+'-block').clone();
    template.find('.root-element-js').prop('id', 'area-'+this.name);
    template.find('.i-element-js').addClass(this.icon);
    template.find('.i-element-js').prop('title',this.title);
    template.find('.title-element-js').text(this.title);
    template.find('.root-element-js').on("click", this.onClick.bind(this));
    parentDev.append(template.find('.root-element-js'));
  }

  onClick(e , cb =null){
    if((this.display === 'none' ||this.display === 'title') && e){
      if(!!cb) cb();
      return;
    }
    epanel = Epanel.getEpanel();
    epanel.showLoader();
    epanel.thisArea = this;
    this.drawAreas();
    this.drawPermissions();
    if(this.link != '#'){
      let areaPanel = new Panel(this.id, this.link, '', this.title,{},'area');
      Epanel.getEpanel().newPage(this.title, [areaPanel], cb);
    }else{
      if(!!cb){
        cb();
      } else{
        epanel.removeLoader();
      }
    }
  }

  drawAreas(){
    //todo: implement this
  }

  drawPermissions(){
    $('#permission-holder').html('');
    this.permissions.forEach((_item,_index)=>{
      _item.draw();
    });
    sidebarAction();
  }

  getAreaPage(cb){
    const connector = Connector.getConnector();

    connector.sendAreaAction(this.link, {}, (_success, _data, _hasPage)=>{
      if(_success){
        if(!!_hasPage){

        }else{
          cb();
        }
      }else{
        this.error = {
          'error': 'error',
          'msg': 'connection error',
          'data': _data
        };
        if(window.debug) console.log(this.error);
        cb();
      }
    });
  }

  updatePermissionsArea(){
    this.permissions.forEach((_item, _index)=>{
      _item.setArea(this);
    });
  }

  addPermission(_permission){
    if(this.permissions == null){
      this.permissions = [];
    }
    this.permissions.push(_permission);
  }
}