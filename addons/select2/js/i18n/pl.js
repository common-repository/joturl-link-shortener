/*
 Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */
(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/pl",[],function(){var e=["znak","znaki","znak\u00f3w"],t=["element","elementy","element\u00f3w"],n=function(t,n){if(t===1)return n[0];if(t>1&&t<=4)return n[1];if(t>=5)return n[2]};return{errorLoading:function(){return"Nie mo\u017cna za\u0142adowa\u0107 wynik\u00f3w."},inputTooLong:function(t){var r=t.input.length-t.maximum;return"Usu\u0144 "+r+" "+n(r,e)},inputTooShort:function(t){var r=
t.minimum-t.input.length;return"Podaj przynajmniej "+r+" "+n(r,e)},loadingMore:function(){return"Trwa \u0142adowanie\u2026"},maximumSelected:function(e){return"Mo\u017cesz zaznaczy\u0107 tylko "+e.maximum+" "+n(e.maximum,t)},noResults:function(){return"Brak wynik\u00f3w"},searching:function(){return"Trwa wyszukiwanie\u2026"}}}),{define:e.define,require:e.require}})();
