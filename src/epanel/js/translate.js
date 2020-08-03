function Localization(_lang, _ns, _defaultNS, _baseURL, cb){

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
      if(cb){
        cb();
      }
    });


}

function getMsg(key, data){
  return i18next.t('msg:' + key, data)
}