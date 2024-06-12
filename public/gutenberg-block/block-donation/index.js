(() => {
    "use strict";
    var e, n = {
        560: (e, n, r) => {
            const t = window.wp.blocks,
                o = window.wp.element,
                l = window.wp.i18n,
                a = window.wp.serverSideRender;
            var i = r.n(a);
            const c = window.wp.blockEditor,
                d = window.wp.components,
                s = JSON.parse('{"u2":"woo-donations-block/woo-donations"}');
            (0, t.registerBlockType)(s.u2, {
                edit: function(e) {
                    console.log(d);
                        let {
                            attributes: n,
                            setAttributes: r
                        } = e;
                        const t = (0, c.useBlockProps)({
                                className: "wdgk-dynamic-block"
                            }),
                            {
                                product_id: b,
                            } = n;
                        return (0, o.createElement)(o.Fragment, null, (0, o.createElement)(c.InspectorControls, null, (0, o.createElement)(d.PanelBody, {
                            title: (0, l.__)("Settings", "woo-donations"),
                            initialOpen: !0
                        }, (0, o.createElement)(d.PanelRow, null, (0, o.createElement)("fieldset", null, (0, o.createElement)(d.TextControl, {
                            label: (0, l.__)("Product ID", "woo-donations"),
                            value: b,
                            onChange: e => {
                                r({
                                    product_id: e
                                })
                            },
                            help: (0, l.__)("Enter product id of which you want to show donation form.", "woo-donations")
                        }))))), (0, o.createElement)("div", t, (0, o.createElement)(d.Disabled, null, (0, o.createElement)(i(), {
                            block: s.u2,
                            skipBlockSupportAttributes: !0,
                            attributes: n
                        }))))
                    }
            })
        }
    },
    r = {};

    function t(e) {
        var o = r[e];
        if (void 0 !== o) return o.exports;
        var l = r[e] = {
            exports: {}
        };
        return n[e](l, l.exports, t), l.exports
    }
    t.m = n, e = [], t.O = (n, r, o, l) => {
        if (!r) {
            var a = 1 / 0;
            for (s = 0; s < e.length; s++) {
                for (var [r, o, l] = e[s], i = !0, c = 0; c < r.length; c++)(!1 & l || a >= l) && Object.keys(t.O).every((e => t.O[e](r[c]))) ? r.splice(c--, 1) : (i = !1, l < a && (a = l));
                if (i) {
                    e.splice(s--, 1);
                    var d = o();
                    void 0 !== d && (n = d)
                }
            }
            return n
        }
        l = l || 0;
        for (var s = e.length; s > 0 && e[s - 1][2] > l; s--) e[s] = e[s - 1];
        e[s] = [r, o, l]
    }, t.n = e => {
        var n = e && e.__esModule ? () => e.default : () => e;
        return t.d(n, {
            a: n
        }), n
    }, t.d = (e, n) => {
        for (var r in n) t.o(n, r) && !t.o(e, r) && Object.defineProperty(e, r, {
            enumerable: !0,
            get: n[r]
        })
    }, t.o = (e, n) => Object.prototype.hasOwnProperty.call(e, n), (() => {
        var e = {
            826: 0,
            431: 0
        };
        t.O.j = n => 0 === e[n];
        var n = (n, r) => {
                var o, l, [a, i, c] = r,
                    d = 0;
                if (a.some((n => 0 !== e[n]))) {
                    for (o in i) t.o(i, o) && (t.m[o] = i[o]);
                    if (c) var s = c(t)
                }
                for (n && n(r); d < a.length; d++) l = a[d], t.o(e, l) && e[l] && e[l][0](), e[l] = 0;
                return t.O(s)
            },
            r = globalThis.webpackChunkdynamic_block = globalThis.webpackChunkdynamic_block || [];
        r.forEach(n.bind(null, 0)), r.push = n.bind(null, r.push.bind(r))
    })();
    var o = t.O(void 0, [431], (() => t(560)));
    o = t.O(o)
})();