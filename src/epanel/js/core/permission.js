class Permission{

  constructor(_title, _permission, _parent, _children,  _area = null){
    this.id = _permission.permission_id;
    this.name = _permission.name;
    this.display = _permission.display;
    this.order = _permission.sort
    this.lvl = _permission.lvl;
    this.link = _permission.home_page;
    this.icon = _permission.icon;
    this.title = _title;
    this.parent = _parent;
    this.children = _children;
    this.area = _area;

    if(this.area && this.lvl == 0){
      this.area.addPermission(this);
    }
  }

  draw(){
    if(this.display === 'none') return;

    const template = $('#epanel-templates #epanel-permission-'+this.display+'-block').clone();
    template.find('.root-element-js').prop('id', 'permission-'+this.name);
    template.find('.i-element-js').addClass(this.icon);
    template.find('.i-element-js').prop('title',this.title);
    template.find('.title-element-js').text(this.title);
    template.find('.root-element-js').on("click", this.onClick.bind(this));
    let parentDev = (this.parent == null)? $('#permission-holder') : $('#permission-'+this.parent.name+ ' ul');
    parentDev.append(template.find('.root-element-js'));

    if(this.children){
      this.children.forEach((_item, _index)=>{
        _item.draw();
      })
    }
  }

  onClick(e, cb= null){

    if(this.link != '#'){
      epanel = Epanel.getEpanel();
      epanel.showLoader();
      epanel.thisPermission = this;
      epanel.thisArea = this.area;
      const elem =  e.target || e.srcElement;
      let permissionPanel = new Panel(this.id, this.link, '', this.title, elem.data,'permission');
      epanel.newPage(this.title, [permissionPanel], ()=>{ epanel.removeLoader();});
      if(!!cb){
        cb();
      }
    }

  }

  addChild(_permission){
    if(this.children == null){
      this.children = [];
    }
    this.children.push(_permission);
  }

  setArea(_area){
    this.area = _area;
  }

}