(function () {
  window.Parsley
    .addValidator('filemaxmegabytes', {
      requirementType: 'string',
      validateString: function (value, requirement, parsleyInstance) {

        var file = parsleyInstance.$element[0].files;
        var maxBytes = requirement * 1048576;
        if (file.length == 0) {
          return true;
        }

        return file.length === 1 && file[0].size <= maxBytes;

      },
      messages: {
        en: 'File size is not allowed ',
        ar: 'حجم الملف المرفوع غير مقبول'
      }
    })
    .addValidator('filemimetypes', {
      requirementType: 'string',
      validateString: function (value, requirement, parsleyInstance) {
        console.log(value,requirement, parsleyInstance); console.log(file);
        var file = parsleyInstance.$element[0].files;
          if (file.length == 0) {
          return true;
        }

        if(file[0].type === ''){
            var allowedExtintion = requirement.match(/\/([^,\s]+)/g);
            var ext =  file[0].name.split('.').pop();
          return allowedExtintion.indexOf('/'+ext) !== -1;
        }

        var allowedMimeTypes = requirement.replace(/\s/g, "").split(',');
         return allowedMimeTypes.indexOf(file[0].type) !== -1;

      },
      messages: {
        en: 'File mime type not allowed',
        ar: 'نوع الملف المرفوع غير مقبول'
      }
    });
}());