/*! MenuIfy by Templateify | v1.0.0 - https://www.templateify.com */
!(function (a) {
  a.fn.menuify = function () {
    return this.each(function () {
      var $t = a(this),
        b = $t.find(".LinkList ul > li").children("a"),
        c = b.length;
      for (var i = 0; i < c; i++) {
        var d = b.eq(i),
          h = d.text();
        if (h.charAt(0) !== "_") {
          var e = b.eq(i + 1),
            j = e.text();
          if (j.charAt(0) === "_") {
            var m = d.parent();
            m.append('<ul class="sub-menu m-sub"/>');
          }
        }
        if (h.charAt(0) === "_") {
          d.text(h.replace("_", ""));
          d.parent().appendTo(m.children(".sub-menu"));
        }
      }
      for (var i = 0; i < c; i++) {
        var f = b.eq(i),
          k = f.text();
        if (k.charAt(0) !== "_") {
          var g = b.eq(i + 1),
            l = g.text();
          if (l.charAt(0) === "_") {
            var n = f.parent();
            n.append('<ul class="sub-menu2 m-sub"/>');
          }
        }
        if (k.charAt(0) === "_") {
          f.text(k.replace("_", ""));
          f.parent().appendTo(n.children(".sub-menu2"));
        }
      }
      $t.find(".LinkList ul li ul").parent("li").addClass("has-sub");
    });
  };
})(jQuery);
