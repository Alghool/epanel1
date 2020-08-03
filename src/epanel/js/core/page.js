class Page {

  constructor(_title){
    $('#panels-holder').html('');
    this.empty = true;
    this.panels = {};
    this.title = _title;

    $('#page-title').text(_title);
  }

  addPanels(_panels, cb= null){
    this.empty = false;
    _panels.forEach((_panel, _index)=>{
      if(_panel.id in this.panels){
        this.panels[_panel.id].onClose();
      }
      _panel.getTemplate((_html)=>{
        $('#panels-holder').append(_html);
        _panel.setPage(this);
        this.panels[_panel.id] = _panel;
        if(_index === _panels.length -1) {
          $('html, body').animate({
            scrollTop: _panel.template.offset().top
          }, 1000);
          if (!!cb) cb(true);
        }
      });
    });
  }

  panelClosed(_panel){
    delete this.panels[_panel.id];
    if(Object.keys(this.panels).length === 0 && this.panels.constructor === Object){
      $('#panels-holder').html('');
      $('#page-title').text('');
      this.empty = true;
    }
  }

  panelRefreshed(_panel){

  }

  getMyPanel(_elem){
    const panelID = _elem.closest('.panel').data('id');
    return this.panels[panelID];
  }

}