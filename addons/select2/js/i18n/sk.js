/*
 Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */
(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/sk",[],function(){var e={2:function(e){return e?"dva":"dve"},3:function(){return"tri"},4:function(){return"\u0161tyri"}};return{errorLoading:function(){return"V\u00fdsledky sa nepodarilo na\u010d\u00edta\u0165."},inputTooLong:function(t){var n=t.input.length-t.maximum;return n==1?"Pros\u00edm, zadajte o jeden znak menej":n>=2&&n<=4?"Pros\u00edm, zadajte o "+e[n](!0)+
" znaky menej":"Pros\u00edm, zadajte o "+n+" znakov menej"},inputTooShort:function(t){var n=t.minimum-t.input.length;return n==1?"Pros\u00edm, zadajte e\u0161te jeden znak":n<=4?"Pros\u00edm, zadajte e\u0161te \u010fal\u0161ie "+e[n](!0)+" znaky":"Pros\u00edm, zadajte e\u0161te \u010fal\u0161\u00edch "+n+" znakov"},loadingMore:function(){return"Na\u010d\u00edtanie \u010fal\u0161\u00edch v\u00fdsledkov\u2026"},maximumSelected:function(t){return t.maximum==1?"M\u00f4\u017eete zvoli\u0165 len jednu polo\u017eku":
t.maximum>=2&&t.maximum<=4?"M\u00f4\u017eete zvoli\u0165 najviac "+e[t.maximum](!1)+" polo\u017eky":"M\u00f4\u017eete zvoli\u0165 najviac "+t.maximum+" polo\u017eiek"},noResults:function(){return"Nena\u0161li sa \u017eiadne polo\u017eky"},searching:function(){return"Vyh\u013ead\u00e1vanie\u2026"}}}),{define:e.define,require:e.require}})();
