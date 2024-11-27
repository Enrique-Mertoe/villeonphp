!function (w, f) {
    f(w);
}(window, function (w) {
    let store = {};
    let main = {
        init() {
            document.addEventListener("DOMContentLoaded", function () {
                main.load_components()
            });
        },
        load_components() {
            $$.post("/control-panel/actions?type=components",
                function success(res) {
                    if (res.ok) {
                        store.componets = res.data;
                    }
                })
        },
        ui: {
            get_component(name) {
                let doc = document.createElement("div");
                doc.innerHTML = store.componets;
                return $$(doc).find(name);
            }
        }
    }

    main.init();

    $$(document).on("click", ".model-btn", function (ev) {
        let comp = main.ui.get_component(".modal-new-model");
        $$("body").append(comp);
        comp.modal("show", function (md) {
            md.onClosed(function (){
                comp.remove();
            })
        })
    })


});