/*
 Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */
(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/ca",[],function(){return{errorLoading:function(){return"La c\u00e0rrega ha fallat"},inputTooLong:function(e){var t=e.input.length-e.maximum,n="Si us plau, elimina "+t+" car";return t==1?n+="\u00e0cter":n+="\u00e0cters",n},inputTooShort:function(e){var t=e.minimum-e.input.length,n="Si us plau, introdueix "+t+" car";return t==1?n+="\u00e0cter":n+="\u00e0cters",n},loadingMore:function(){return"Carregant m\u00e9s resultats\u2026"},
maximumSelected:function(e){var t="Nom\u00e9s es pot seleccionar "+e.maximum+" element";return e.maximum!=1&&(t+="s"),t},noResults:function(){return"No s'han trobat resultats"},searching:function(){return"Cercant\u2026"}}}),{define:e.define,require:e.require}})();
