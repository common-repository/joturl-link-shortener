/*
 Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */
(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/et",[],function(){return{inputTooLong:function(e){var t=e.input.length-e.maximum,n="Sisesta "+t+" t\u00e4ht";return t!=1&&(n+="e"),n+=" v\u00e4hem",n},inputTooShort:function(e){var t=e.minimum-e.input.length,n="Sisesta "+t+" t\u00e4ht";return t!=1&&(n+="e"),n+=" rohkem",n},loadingMore:function(){return"Laen tulemusi\u2026"},maximumSelected:function(e){var t="Saad vaid "+
e.maximum+" tulemus";return e.maximum==1?t+="e":t+="t",t+=" valida",t},noResults:function(){return"Tulemused puuduvad"},searching:function(){return"Otsin\u2026"}}}),{define:e.define,require:e.require}})();
