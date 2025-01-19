!function (w, f) {
    // $$(document)
    f(w);
}(window, function (w) {
    "use strict";
    let store = {};
    let main = {
        init() {
            document.addEventListener("DOMContentLoaded", function () {
                main.admin_config();
                main.load_components();
                main.model_handler();
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
        admin_config() {
            $$(".secure-view").modal("show", function (md) {
                md.onClose(function () {
                    if (md.body.find('form input:checked').size) {
                        $$.post("/control-panel/actions?type=disable_secure"
                        )
                    }

                });
                md.onClosed(function () {
                    $$(".secure-view").remove();
                });

                md.body.find('[data-smv-next]').click(function (ev) {
                    ev.preventDefault();
                    if (!$$(".__can-connect").size) {
                        main.settings.db_config()
                    } else {
                        main.settings.create_admin();
                    }
                })
            })
        },
        ui: {
            get_component(name) {
                let comp = $$(document.createElement("div"))
                    .aClass("comp-body").html(store.componets)
                return $$(document.createElement("div")).html(comp)
                    .find(`.comp-body > ${name}`);
            },
            btnLoader(btn) {
                btn = $$(btn);
                return {
                    load() {
                        btn.aClass('loading');
                        return this;
                    },
                    reset() {
                        btn.rClass('loading');
                        return this;
                    }
                }
            }
        },
        settings: {
            db_config(e) {
                let comp = main.ui.get_component(".comp-db-config");
                $$("body").append(comp);

                const args = e instanceof Element ?
                    $$(e).attr("href").substring(2).split(',') : null;
                Modal(comp)
                    .onDismiss(function (ev) {
                        ev.clear()
                    })
                    .onOpen(function (ev) {
                        let modal = this;
                        if (args) {
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
                                    btn.reset();
                                    if (res.ok) {
                                        modal.dismiss();
                                        location.reload()
                                    }
                                });
                        });
                    })
                    .show();
            },
            create_admin() {
                let comp = main.ui.get_component(".admin-form");
                $$("body").append(comp);
                Modal(comp)
                    .onOpen(function (ev) {
                        let modal = this;
                        this.view.find("form").on("submit", function (ev) {
                            ev.preventDefault();
                            let self = this;
                            let btn = main.ui.btnLoader($$(this).find("button[type=submit]")).load();
                            let fd = new FormData(this);
                            $$.post({url: "/control-panel/actions?type=create_super_admin", data: fd})
                                .then(res => {
                                    btn.reset();
                                    if (res.ok) {
                                        modal.dismiss();
                                        location.reload()
                                    }
                                });
                        });

                    })
                    .show();
            }
        },
        model_handler() {
            let cols = [];
            const ColField = function (default_view) {
                let view = default_view ? default_view : main.ui.get_component(".model-field");
                let oDl;
                const events = {
                    onDelete(cb) {
                        oDl = cb
                    }
                };
                // view.find("input:not([name=col-name])").each(function () {
                //     let name = $$(this).attr("name"),
                //         prefix = view.find("[name=col-name]").val();
                //     $$(this).data("name", name)
                //     $$(this).attr("name", (prefix ? prefix + "_" : prefix) + name);
                // });
                // view.find("[name=col-name]").on("input", function (ev) {
                //     let prefix = this.value;
                //     view.find("input:not(.col-name)").each(function () {
                //         let attr = $$(this).data("name");
                //         $$(this).attr("name", prefix + "_" + attr);
                //     })
                // });
                view.find("[data-smv-trigger=model-field-remove]")
                    .on("click", function (ev) {
                        ev.preventDefault();
                        view.remove();
                        delete cols[index];
                    });
                const options = {
                    view,
                    data() {
                    }
                };
                // cols[index] = options;
                return options;
            };
            const get_data = function (form) {
                let fd = {
                    name: form.find(".model-name-input").val(),
                    alias: form.find(".model-alias-input").val(),
                };
                let cols = [];
                form.find(".model-field.model-col-item").each(function () {
                    let col = $$(this);
                    let d = {
                        name: col.find("[data-col-name]").val(),
                        type: col.find("[data-col-type]").val(),
                        defaultVal: col.find("[data-col-default]").val(),
                        primary: col.find("[data-col-primary]").checked(),
                        unique: col.find("[data-col-unique]").checked(),
                        nullable: col.find("[data-col-nullable]").checked(),
                        auto: col.find("[data-col-auto]").checked(),
                    };
                    if (!d.name)
                        col.find(".field-alert").show().txt("Column name is required!")
                    else {
                        col.find(".field-alert").hide();
                    }
                    cols.push(d);
                });
                fd.cols = cols;
                return fd;
            };
            const handle_new_model = (view, modal) => {
                view = $$(view);
                let btn = view.find("[data-smv-submit]"),
                    loader = main.ui.btnLoader(btn),
                    form = view.find(".form-new-model"),
                    nameInput = view.find(".model-name-input"),
                    fAdd = view.find("[data-smv-trigger=add-model-field]"),
                    fAlert = view.find(".model-alert");
                let data = {};

                nameInput.on("input", function (ev) {
                    ev.preventDefault();
                    const form = $$(this).closest("form");
                    const modelAliasInput = form.find(".model-alias-input");
                    const modelWarning = form.find(".model-name-alert");
                    const modelName = $$(this).val();
                    const formatAlias = (value) => {
                        return value
                            .split(" ")
                            .map((word, index) => index === 0
                                ? word.toLowerCase()
                                : word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                            .join("");
                    };
                    if (/\s/.test(modelName))
                        modelWarning.show().txt("Model names should not contain spaces")
                    else
                        modelWarning.hide();
                    const aliasPlaceholder = modelName.toLowerCase().endsWith("s")
                        ? modelName.toLowerCase()
                        : modelName ? `${modelName.toLowerCase()}s` : '';
                    modelAliasInput.attr("placeholder", aliasPlaceholder || "Table Name");
                    if (!modelName.trim())
                        btn.aClass("disabled")
                    else
                        btn.rClass("disabled")


                });
                view.find(".model-field").each(function () {
                    ColField($$(this));
                });
                fAdd.on("click", function (ev) {
                    ev.preventDefault();
                    let field = ColField();
                    $$(".model-fields").append(field.view);
                    const scrollableDiv = $(view.find(".modal-body")[0]);
                    scrollableDiv.scrollTop(scrollableDiv[0].scrollHeight);
                });

                form.on("submit", function (ev) {
                    ev.preventDefault();
                    loader.load();
                    let data;
                    try {
                        data = get_data($$(this))
                    } catch (e) {
                        return
                    }
                    // let data = new FormData(this);
                    $$.post({url: "/control-panel/actions?type=new_model", data})
                        .then(res => {
                            loader.reset();
                            if (res.ok)
                                modal.reset();
                            else
                                alert(res.data)
                        });
                });
            }

            $$(document).on("click", ".model-btn", function (ev) {
                ev.preventDefault()
                let comp = main.ui.get_component(".modal-new-model");
                $$("body").append(comp);
                Modal(comp)
                    .onDismiss(ev => {
                        ev.clear();
                    })
                    .onOpen(function () {
                        handle_new_model(this.view, this);
                    })
                    .show();
            });
        },

    }

    main.init();


    $$(document).on("click", "[data-smv-component=db-config", function (ev) {
        ev.preventDefault()
        main.settings.db_config(this);

    });
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

    $$(document).on("click", function (ev) {
        if (!$$(ev.target).closest(".field-options").size && !$$(ev.target).is("[data-smv-toggle=dropdown]")) {
            $$(".field-options").rClass("show")
        }
        if (!$$(ev.target).closest(".menu").size && !$$(ev.target).is("[data-smv-toggle=dropdown]")) {
            $$(".menu-options").rClass("show");
        }
    });

    const ModelView = function () {
        return new ModelView.init(...arguments);
    };


    (ModelView.init = function (trigger, view, table) {
        view = $$(view);
        this.trigger = trigger = $$(trigger);
        this.table = table;
        this.view = view;
        let body = view.find(".wizard-body");
        $$(".model-view-container").html(view);
        view.find(".model-table-name").txt(table.name);
        view.find(".wizard-body").html("Please Wait...");
        let fd = new FormData();
        fd.append("table", table.name)
        $$.post({
            url: "/control-panel/actions?type=table_info",
            data: fd
        }).then(res => {
            if (res.ok) {
                let d = document.createElement("div");
                d.innerHTML = res.data;
                body.html($$(d).find(".wizard-body").html());
            }
        })

    }).prototype = {};

    const Table = function () {
        return new Table.init(...arguments);
    };
    (Table.init = function (view) {
        this.view = view = $$(view);
        this.name = view.find(".model-name").txt();
        let self = this;
        view.on("click", function () {
            ModelView(this, main.ui.get_component(".model-view"), self);
        });
        view.find('[data-t-del]').on("click", function (ev) {
            ev.stopPropagation();
            if (confirm("This table is going to be deleted")) {
                let fd = new FormData();
                fd.append("table", view.find(".model-name").txt())
                $$.post({
                    url: '/control-panel/actions?type=table_delete',
                    data: fd
                }).then(res => {
                    if (res.ok) {
                        view.remove()
                    } else {
                        alert(res.data)
                    }
                })

            }
        })
    }).prototype = {
        constructor: Table,
    };
    w.Table = Table;
    $$('.table-item').each(function () {
        Table(this);
    });


});
