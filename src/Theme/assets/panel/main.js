!function (w, f) {
    // $$(document)
    f(w);
}(window, function (w) {
    "use strict";
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
            },
            btnLoader(btn) {
                btn = $$(btn);
                return {
                    load() {
                        btn.aClass('loading');
                        return this;
                    },
                    dismiss() {
                        btn.rClass('loading');
                        return this;
                    }
                }
            }
        }
    }

    main.init();

    $$(document).on("click", ".model-btn", function (ev) {
        ev.preventDefault()
        let comp = main.ui.get_component(".modal-new-model");
        $$("body").append(comp);
        Modal(comp)
            .onDismiss(ev => {
                ev.clear();
            })
            .onOpen(function () {
                let self = this;
                this.view.on("submit", ".form-new-model", function (ev) {
                    ev.preventDefault();
                    let btn = main.ui.btnLoader($$(this).find("button[type=submit]")).load();
                    let data = new FormData(this);
                    $$.post({url: "/control-panel/actions?type=new_model", data})
                        .then(res => {
                            btn.dismiss();
                            if (res.ok)
                                self.dismiss();
                            else
                                alert(res.data)
                        });
                });
            })
            .show();
    });
    $$(document).on("click", "[data-smv-component=db-config", function (ev) {
        ev.preventDefault()
        let comp = main.ui.get_component(".comp-db-config");
        $$("body").append(comp);

        const args = $$(this).attr("href").substring(2).split(',')
        Modal(comp)
            .onDismiss(function (ev) {
                ev.clear()
            })
            .onOpen(function (ev) {
                let modal = this;
                if (args){
                    this.view.find('[name=db_server]').val(args[0]);
                    this.view.find('[name=db_user]').val(args[1]);
                    this.view.find('[name=db_name]').val(args[2]);
                }
                this.view.find(".form-db-config").on("submit", function (ev) {
                    ev.preventDefault();
                    let self = this;
                    let btn = main.ui.btnLoader($$(this).find("button[type=submit]")).load();
                    let fd = new FormData(this);
                    $$.post({url: "/control-panel/actions?type=db_config", data: fd})
                        .then(res => {
                            btn.dismiss();
                            if (res.ok) {
                                modal.dismiss();
                                location.reload()
                            }
                        });
                });
            })
            .show();


    })
    $$(document).on("click", "[data-smv-toggle=model-item", function (ev) {
        ev.preventDefault()
    });
    $$(document).on("click", "[data-smv-toggle=dropdown]", function (ev) {
        ev.preventDefault();
        let drop = $$(this).next();
        drop.tClass("show")
        let
            params = drop.params(), offset = drop.offset();
        console.log($$(this).offset().right)
        if (offset.right < 0) {
            drop.css({
                right: `.5rem`
            })
        }

    });
    $$(document).on("click", ".field-option", function (ev) {
        ev.preventDefault();
        let drop = $$(this).closest(".field-options");
        drop.rClass("show");

    });
    $$(document).on("click", "[data-smv-trigger=add-model-field]", function (ev) {
        ev.preventDefault();
        let drop = main.ui.get_component(".model-field");
        $$(".model-fields").append(drop);

    });
    $$(document).on("click", "[data-smv-trigger=model-field-remove]", function (ev) {
        ev.preventDefault();
        $$(this).closest(".model-field").remove();

    });

    $$(document).on("click", function (ev) {
        if (!$$(ev.target).closest(".field-options").size && !$$(ev.target).is("[data-smv-toggle=dropdown]")) {
            $$(".field-options").rClass("show")
        }
        if (!$$(ev.target).closest(".menu").size && !$$(ev.target).is("[data-smv-toggle=dropdown]")) {
            $$(".menu-options").rClass("show");
        }
    })


});