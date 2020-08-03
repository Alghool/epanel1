// settings///////
var lang = 'english'; //todo: add multi language support
var debug = false; //todo: active this
// variables//////
var baseurl; // used for pages request eq: area, page, permission //todo: send those by encrypted way from server
var actionUrl; //used for action request eq: refreash panel
var testUrl; //for test porpuse
var rootlvl;// get the root lvl for referance

//request param : to save the current stuts should always updated with every request
var activeLink; //to save the last requested page link todo:change the names of page(general maining) and (epanel settings page)
var activeArea; //to save the active area now
var activePermission = '0'; //to save the active permission 0 for no active permission
var activeType; //to save the last active type request (permission, area, page)


$(window).on('load', function() { //to initialize the epanel params
  baseurl = $('#base-info').attr('baseurl') + 'epanel/page/';
  actionUrl = $('#base-info').attr('baseurl') + 'epanel/action/';
  testUrl = $('#base-info').attr('baseurl') + 'epanel/test/';
  activeLink = $('#root-area').attr('url');
  activeArea = $('#root-area').attr('areaId');
  activeType = 'area';
  rootlvl = $('#root-area').attr('lvl');

  //todo: add setting intialize here
  callPage();
});

// call page///////////////////////////////////////////////////////////////////////////////////////////////
function requestPage(caller){ //when request epanel setting page //todo: change this to request interface
  var link = $(caller).attr('action');
  activeLink = link;
  activeType = 'page';
  activeArea = $('#root-area').attr('areaId'); //to set active area at root area to be apple to navigate
  callPage();
}
//todo: should add single server portal and multi interface
function callPage() { //todo:change active params after the ressponse successful not before the request
  //type should be removed
  //add loading panel
  $('.loading-panel').removeClass('hidden'); //todo: only show the loading here alter the content only after response successful
  $('.page-header').addClass('hidden');
  $('.content').addClass('hidden');

  //remove extra panels
  $(".extra-panels .wrapper").remove();

  $('.permission').removeClass('active');
  //console.log(link);
  //console.log(type);
  //console.log(id);//id is not used any more
  $.ajax({
    type: "POST",
    url: baseurl,
    dataType:'json',
    data: ({'link': activeLink, 'type': activeType, 'permission': activePermission, 'area': activeArea})
  })
    .done(function(data) {
      console.log(data);
      $('.content #main-panel .this-action').attr('action', activeLink);//to activate reload action
      if(activeType == 'area'){
        buildAreaPage(data);
      }else if(activeType == 'permission'){
        buildPermissionPage(data);
      }else if(activeType == 'page'){
        buildPage(data);
      }
      //todo:add msg handler here when call the page ref:7
    })
    .fail(function(e){
      // //debug the output
      console.log(e);
      $('.loading-panel').addClass('hidden');
      $('.page-header').removeClass('hidden');
      $('.content .panel-body').remove();
      var panel = '<div class="panel-body"></div>';
      $('.content .panel').append(panel);
      $('.content .panel-body').append(e.responseText);
      $('.content').removeClass('hidden');

      ////////////////////////////////////////////////////
      //todo: handle errors
      console.log(e);
      console.log(e.status);
  });
}

// build page/////////////////////////////////////////////////////////////////////////////////////////////////
function buildPage(data){
  activePermission = '0';

  var icon = '<i class="' + data.page.icon +' position-left"></i>'
  var name = data.page.name;
  var lvl = data.area.lvl;

  buildAreasPermissionsMenu( data)

  //title rewrite
  $('.page-header h4 i').remove();
  $('.page-header h4').prepend(icon);
  $('.page-header h4 span').text(name);

  //remove loading panel
  $('.loading-panel').addClass('hidden');
  $('.page-header').removeClass('hidden');
  if(data.content != "none"){
    $('.content #main-panel .panel-body').remove();
    var panel = '<div class="panel-body"></div>';
    $('.content #main-panel ').append(panel);
    $('.content #main-panel .panel-body').append(data.content);
    $('.content').removeClass('hidden');
    $('.extra-panels').html('<div class="wrapper"></div>');
  }

}

function buildAreaPage(data){
  activePermission = '0';

  var icon = '<i class="' + data.area.icon +' position-left"></i>'
  var name = data.area.name;


  buildAreasPermissionsMenu(data);

  //title rewrite
  $('.page-header h4 i').remove();
  $('.page-header h4').prepend(icon);
  $('.page-header h4 span').text(name);

  //remove loading panel
  $('.loading-panel').addClass('hidden');
  $('.page-header').removeClass('hidden');
  if(data.content != "none"){
    $('.content #main-panel .panel-body').remove();
    var panel = '<div class="panel-body"></div>';
    $('.content #main-panel ').append(panel);
    $('.content #main-panel .panel-body').append(data.content);
    $('.content').removeClass('hidden');
    $('.extra-panels').html('<div class="wrapper"></div>');
  }

}

function buildPermissionPage(data){
  var icon = '<i class="' + data.permission.icon +' position-left"></i>';
  var name = data.permission.name;
  var lvl = data.permission.lvl;

  $('#permission-'+ data.permission.id).addClass('active');

  //title rewrite
  $('.page-header h4 i').remove();
  $('.page-header h4').prepend(icon);
  $('.page-header h4 span').text(name);

  //remove loading panel
  $('.loading-panel').addClass('hidden');
  $('.page-header').removeClass('hidden');

  //content
  if(data.content != "none"){
    $('.content #main-panel .panel-body').remove();
    var panel = '<div class="panel-body"></div>';
    $('.content #main-panel').append(panel);
    $('.content #main-panel .panel-body').append(data.content);

    //add extra panels wrapper
    $('.extra-panels').html('<div class="wrapper"></div>');

    //add subpermissions
    if(data.permissions != "none"){
      var options = "";
      for(var i = 0 ; i < data.permissions.length; i++){
        options += '<option value="' +data.permissions[i].home_page +'">' +data.permissions[i].name +'"</option>';
      }
      var subpermissions = '<select name="subpermissions" class="subpermissions">' +
        '<option disabled selected>اختر القسم</option>' +
          options +
        '</select>'
      $('.content  #main-panel .panel-body').prepend(subpermissions);
    }

    $('.content').removeClass('hidden');
  }

}

function buildAreasPermissionsMenu(data){
  var lvl = data.area.lvl;

  //remove submenus
  if(lvl == rootlvl ){
    $('.areas-holder.first-lvl .area-title').text(' اختر القسم');
    $('.areas-holder').each(function(){
      var thislvl = $(this).attr('lvl');
      if (thislvl > (parseInt( rootlvl) +1) ){
        console.log(rootlvl +1);
        $(this).remove();
      }
    });
  }
  else{
    if(data.area.display != '4' && data.area.id != $('.second-lvl .area-title').attr('areaid')){

      $('.second-lvl').remove();
    }
    $('.areas-holder').each(function(){
      var thislvl = $(this).attr('lvl');
      if (thislvl > lvl){
        $(this).remove();
      }
    });
  }

  if(data.subareas != "none" && data.area.id != $('#root-area').attr('areaid') /*do not generate the list if this parent is the root*/ && data.area.display == '4'){

    var newlvl = parseInt(data.area.lvl) + 1;
    var html = '<li class="dropdown  areas-holder second-lvl" lvl="' + newlvl + '">'
      +'<a  class="dropdown-toggle" data-toggle="dropdown"><span class="area-title"> اختر القسم</span><span class="caret"></span></a>'
      +'<ul class="dropdown-menu dropdown-menu-right">';
    for(var i = 0; i < data.subareas.length; i++){
      html += '<li><a class="area-selector"  areaId="' +data.subareas[i].id +'" url="'+data.subareas[i].home_page +'">' + data.subareas[i].name +'</a>';
    }
    html += '</ul></li>';
    //todo: build this twigy helper function as JS function and ignor the twiggy helper
    // {% for area in role.areas.subareas %}
    // {{ getAreas(area) }} {#call the recursive function#}
    // {% endfor %}
    $('#area-bar').append(html);
    setTimeout(function(){
      $('.second-lvl .area-selector').on('click', function(e){
        areaSelected(e)
      });
    },100);
  }
  //permission handling
  $('.permissions-holder').remove();
  var holder = '<ul lvl="0" class="navigation navigation-main navigation-accordion permissions-holder"></ul>';
  $('#permission-wrapper').append(holder);
  if(data.permissions != "none"){
    buildPermission(data.permissions);
  }

}

// build permissions//////////////////////////////////////////////////////////////////////////////////////////
  function buildPermission(permissions){
    var rootlvl = 50;
    for(var i = 0; i < permissions.length; i++)
    {
      var permission = permissions[i];
      if(permission.lvl < rootlvl)
        rootlvl = permission.lvl;
    }
    var  holder = $('.permissions-holder');
    holder.attr('lvl', rootlvl);

    for(var i = 0; i < permissions.length; i++ ){
      var permission = permissions[i];
      if(permission.lvl == rootlvl){
       // console.log(permissions);
        var html = '<li class="permission" lvl="'+permission.lvl+'" permissionId="'+ permission.id+'" id="permission-'+permission.id+'">' +
          '<a url="'+permission.home_page+'"><i class="'+permission.icon+'"></i> <span>'+permission.name+'</span></a>' +
          '</li>';
        holder.append(html);
        if(permission.display == '2')
          drawPermissions(permission.id, permissions, permission.lvl);

      }
    }
    permissionsEvents();
  }

  function drawPermissions(parentId, permissions, parentlvl){
    //console.log("draw has called from ");
    //console.log(parentId);
    //console.log("*************");
    for(var i = 0; i <permissions.length; i++){
      var permission = permissions[i];

      if( permission.parent == parentId ){

        //add ul to add subpermissions
        if($('#permission-' +parentId +' .permissions-holder').length == 0){
         //console.log("ul added");
          $('#permission-' +parentId).append('<ul class="permissions-holder" lvl="'+permission.lvl+'"></ul>');
        }
        //console.log("adding permission :");
        //console.log(permission);
        var  holder = $('#permission-' +parentId +'>.permissions-holder');
        var html = '<li class="permission" lvl="'+permission.lvl+'" permissionId="'+ permission.id+'"  id="permission-'+permission.id+'">' +
          '<a url="'+permission.home_page+'"><i class="'+permission.icon+'"></i> <span>'+permission.name+'</span></a>' +
          '</li>';
        holder.append(html);
        //console.log(html);
        if(permission.display == '2')
          drawPermissions(permission.id, permissions, permission.lvl);
        }
    }
    //console.log("****exit from " +parentId + "**********************************************************************************************");

  }

// actions//////////////////////////////////////////////////////////////////////////////////////////
function confirmAction(sender){
  var caller = $(sender)
  var msg = caller.attr('msg');
  var action = caller.attr('action');
  //var newpanel = caller.attr('innew');

  if(typeof msg === 'undefined' || !msg)
    sendAction(action, caller);
  else{
    $.msgBox({
      title: "Confirm",
      content: msg,
      type: "confirm",
      buttons: [{ value: "sure" }, { value: "cancel" }],
      success: function (result) {
        if (result == "sure") {
          sendAction(action, caller);
        }
      }
    });
  }
}

function formAction(caller){
  $('.loading-panel').removeClass('hidden');
  //get form data
  var action = caller.attr('link');
  var form = caller.closest('form');
  var id = form.attr('id');
  var myForm = document.getElementById(id);
  var formdata = new FormData(myForm);


  //get active data
  var type = activeType;
  var id; //id is not used any more
  var link;
  // if(type == 'area'){
  //   id = activeArea;
  // }else{
  //   id = activePermission;
  // }
  link = action;

  formdata.append("link", link);
  formdata.append("type", type);
  formdata.append("permission", activePermission);
  formdata.append("area", activeArea);
  //formdata.append("id", id);

  $.ajax({
    type: "POST",
    url: actionUrl,
    contentType : false,
    processData : false,
    dataType:'json',
    data: formdata
  })
  .done(function(data) {
    successAction(data, caller);
    $('.loading-panel').addClass('hidden');
  })
  .fail(function(e){
    failedAction(e, caller)
    $('.loading-panel').addClass('hidden');
  });
};

function sendAction(action, caller){
  $('.loading-panel').removeClass('hidden');
  //get active data
  var type = activeType;
  var id;
  var link;
  // if(type == 'area'){
  //   id = activeArea;
  // }else{
  //   id = activePermission;
  // }
  link = action;

  $.ajax({
    type: "POST",
    url: actionUrl,

    dataType:'json',
    data: ({'link': link, 'type': type, 'permission': activePermission, 'area': activeArea})

  })
    .done(function(data) {
      successAction(data, caller);
      $('.loading-panel').addClass('hidden');
    })
    .fail(function(e){
      failedAction(e, caller)
      $('.loading-panel').addClass('hidden');
    });
}

function specialAction(action, type, data, cb){
  $('.loading-panel').removeClass('hidden');
  var link = action;

  $.ajax({
    type: "POST",
    url: actionUrl,

    dataType:'json',
    data: ({'link': link, 'type': type, 'data': data, 'permission': activePermission, 'area': activeArea})

  })
    .done(function(data) {
      cb(data, 'success');
      $('.loading-panel').addClass('hidden');
    })
    .fail(function(e){
      cb(e, 'fail');
      $('.loading-panel').addClass('hidden');
    });

}
// response handling////////////////////////////////////////////////////////////////////////////////

function successAction(data, caller){
  // console.log(data);
  if(data.success == true){
    //what to do with its panel
    switch(caller.attr('reload')){
      case "destroyandreloadmain":
        var mainAction = $('#main-panel').find('.this-action').attr('action');
        var mainSimulator = $('#main-panel').find('.caller-simulator'); //caller-simulator for reload content without actual caller
        $(".extra-panels .wrapper").remove();
        $('.extra-panels').html('<div class="wrapper"></div>');
        sendAction(mainAction, mainSimulator); //reload callback
        break;
      case "destroy":
        var panel = caller.closest('.panel');
        if (panel.attr('id') !== 'main-panel')
          panel.remove();
        break;
      case "reloadandMain": //reload the main panel first
        var mainAction = $('#main-panel').find('.this-action').attr('action');
        var mainSimulator = $('#main-panel').find('.caller-simulator'); //caller-simulator for reload content without actual caller
        sendAction(mainAction, mainSimulator); //reload callback
      case "reload":
        var reloadAction = caller.closest('.panel').find('.this-action').attr('action');
       if(reloadAction != 'notReloadable') { //solve to eliminate notreloadable as soon as possible
         var callerSimulator = caller.closest('.panel').find('.caller-simulator'); //caller-simulator for reload content without actual caller
         var panel = caller.closest('.panel');
         if (panel.attr('id') == 'main-panel') {
           $(".extra-panels .wrapper").remove();
           $('.extra-panels').html('<div class="wrapper"></div>');
         }
         sendAction(reloadAction, callerSimulator); //reload callback
       }
        break;
      case "simulator":
        //this is reload callback
        break;
      default:
        //todo: add some method  to be sure about it nonref
        break;
    }


    //what to do with returning content
    if(!(typeof data.content === 'undefined' || !data.content))
    {
      var panel = (!caller.attr('panel') || typeof caller.attr('panel') === 'undefined')?
        caller.closest('.panel').attr('id'): caller.attr('panel');
      buildPanel(panel, data.content, caller.attr('action'));
    }

  }else{
    console.log(data);

    //todo: add debug option ref:6
   // return false
  }
  //add msg handler
  if(!(typeof data.msg === 'undefined' || !data.msg)) {
    $(function () {
      new PNotify({
        type: data.msg.type,
        text: data.msg.text,
        stack: stack_context
      });
    });
  }
}

function failedAction(e, caller){
  // //debug the output
  $('.loading-panel').addClass('hidden');
  $('.page-header').removeClass('hidden');
  $('.content .panel-body').remove();
  var panel = '<div class="panel-body"></div>';
  $('.content .panel').append(panel);
  $('.content .panel-body').append(e.responseText);
  $('.content').removeClass('hidden');

  ////////////////////////////////////////////////////
  //todo: handle errors
  console.log(e);
  console.log(e.status);
}

// build main //////////////////////////////////////////////////////////////////////////////////////
function buildPanel(panelID, content, link, cb){
  var panel = $('#'+panelID);
  if(panel.length == '0'){
      var panelhtml = ' <div id="'+panelID+'" class="panel panel-flat" >'
        +'<div class="panel-heading">'
        +'<h5 class="panel-title"></h5>'
        +'<div class="heading-elements">'
        +'<ul class="icons-list">'
        +'<li><a data-action="collapse"></a></li>'
        +'<li><a data-action="close"></a></li>'
        +'</ul></div></div>'
        +'<div class="this-action" action ="'+link+'"></div>'
        +'<div class="caller-simulator" reload="simulator"></div>'
        +'<div class="panel-body">'
        +content
        +'</div></div>';
    $('.extra-panels>.wrapper').append(panelhtml);

      addCollapse(panelID);

  }else{
    panel.find('.this-action').attr('action', link);
    panel.find('.panel-body').remove();
    panel.append('<div class="panel-body"></div>');
    panel.find('.panel-body').html(content);
  }
  if(!(typeof cb === 'undefined' || !cb)) {
    cb(panelID);
  }
  $('html, body').animate({
    scrollTop: $('#'+panelID).offset().top
  }, 1000);
}

// events//////////////////////////////////////////////////////////////////////////////////////////

  $('.area-selector').on('click', function(e){
    areaSelected(e)
  });

  function areaSelected(e){
    var selected = $(e.target);
    var url = selected.attr('url');
    var id = selected.attr('areaid');
    var name = selected.text();
    var title = selected.closest(".areas-holder").find('.area-title');
    console.log("====area: " +id+"====================================================");
    if(url != '#'){
      title.text(name);
      title.attr('url', url);
      title.attr('areaId', id);
      title.addClass('area-selector');  //todo: add multi level support ref:2

      activeLink = url;
      activeArea = id;
      activeType = 'area';
      callPage();
      //update permissions
      //call url
    }
  }


  function permissionsEvents(){
    sidebarAction();
    $('.permission').on ('click' , function(e){

      var selected = $(e.target).closest('li');
      var id = selected.attr('permissionId');
      var anckor = selected.find('a');
      var url = anckor.attr('url');
      console.log("====permission: " + id + "====================================================");
      console.log(url);
      if(url != '#'){
        activeLink = url;
        activePermission = id;
        activeType = 'permission';
        callPage( );
      }
    });
  };

  $('#main-panel .reload-panel').on ('click' , function(e){
    //remove extra panels if reload the main panel
    $(".extra-panels .wrapper").remove();
    $('.extra-panels').html('<div class="wrapper"></div>');

    var caller = $(e.target).closest('.panel');
    var action = caller.find('.this-action').attr('action');
    var callerSimulator = caller.find('.caller-simulator'); //caller-simulator for reload content without actual caller
    sendAction(action, callerSimulator);


  });

function addCollapse(selector){
  $('#' +selector +' [data-action=collapse]').click(function (e) {
    e.preventDefault();
    var $panelCollapse = $(this).parent().parent().parent().parent().nextAll();
    $(this).parents('.panel').toggleClass('panel-collapsed');
    $(this).toggleClass('rotate-180');

    containerHeight(); // recalculate page height

    $panelCollapse.slideToggle(150);
  });

  // Panels
  $('.panel [data-action=close]').click(function (e) {
    e.preventDefault();
    var $panelClose = $(this).parent().parent().parent().parent().parent();

    containerHeight(); // recalculate page height

    $panelClose.slideUp(150, function() {
      $(this).remove();
    });
  });
}


//helpers//////////////////////////////////////////////////////////////////////////////////////////
function reloadJS(jsName){
  var js = $('#'+jsName);
  $('#'+jsName).remove;
  $('#script-holder').append(js);
}

function extractHostname(url) {
  var hostname;
  //find & remove protocol (http, ftp, etc.) and get hostname

  if (url.indexOf("://") > -1) {
    hostname = url.split('/')[2];
  }
  else {
    hostname = url.split('/')[0];
  }

  //find & remove port number
  hostname = hostname.split(':')[0];
  //find & remove "?"
  hostname = hostname.split('?')[0];

  return hostname;
}

function extractRootDomain(url) {
  var domain = extractHostname(url),
    splitArr = domain.split('.'),
    arrLen = splitArr.length;

  //extracting the root domain here
  if (arrLen > 2) {
    domain = splitArr[arrLen - 2] + '.' + splitArr[arrLen - 1];
  }
  return domain;
}

stack_context = {
  "dir1": "up",
  "dir2": "left",
  "push":"down"
};