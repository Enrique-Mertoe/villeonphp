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
                });
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
            },
            iconLoader(btn) {
                btn = $$(btn);
                return {
                    load() {
                        btn.aClass('show');
                        return this;
                    },
                    reset() {
                        btn.rClass('show');
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
                    mode: form.data("mode")
                };
                if (form.data("mode") === "edit") {
                    fd.initials = {
                        name: form.data("ini-name"),
                        alias: form.data("ini-alias"),
                    }
                }
                let cols = [];
                form.find(".model-field.model-col-item").each(function () {
                    let col = $$(this);
                    let d = {
                        name: col.find("[data-col-name]").val(),
                        type: col.find("[data-col-type]").txt().trim().toUpperCase(),
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
                    fAlert = view.find(".general-msg-box");
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
                    fAlert.hide();
                    $$.post({url: "/control-panel/actions?type=new_model&mode=" + form.data("mode"), data})
                        .then(res => {
                            loader.reset();
                            if (res.ok)
                                modal.reset();
                            else
                                fAlert.show().html(res.data)
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

            $$(".model-item").each(function () {
                let self = $$(this);
                let name = self.find("[data-model=name]"),
                    table = self.find("[data-model=table]"),
                    edit = self.find("[data-model=edit]"),
                    del = self.find("[data-model=delete]");
                del.on("click", function (e) {
                    e.preventDefault();
                    confirm("Confirm deletion of model: " + name.txt(), _ => {
                        Request({
                            target: "models",
                            action: "del",
                            args: {
                                name: name.txt().trim(),
                                table: table.txt().trim()
                            }
                        }, res => {
                            alert(res.data + "");
                            if (res.ok)
                                self.remove()
                        });
                    });
                });
                edit.on("click", function (e) {
                    e.preventDefault();
                    DialogLoader();
                    Request({
                        target: "models",
                        action: "info",
                        args: {
                            name: name.txt().trim(),
                            table: table.txt().trim()
                        }
                    }, res => {
                        DialogLoader();
                        let comp = main.ui.get_component(".modal-new-model");
                        comp.find(".modal-content").html($(res.data).find(".modal-content").html())
                        $$("body").append(comp);
                        Modal(comp)
                            .onDismiss(ev => {
                                ev.clear();
                            })
                            .onOpen(function () {
                                handle_new_model(this.view, this);
                            })
                            .show();
                    })
                });
            });
            $$("[data-model-sync]").on("click", function (ev) {
                ev.preventDefault();
                let btn = main.ui.iconLoader(this);
                confirm("Ensure data backup before proceeding", _ => {
                    btn.load();
                    Request({
                        target: "models",
                        action: "sync"
                    }, res => {
                        btn.reset();
                        alert(res.data)
                    });
                });
            });
        },

    }

    const confirm = (message, handler) => {
        let comp = main.ui.get_component(".d-confirm");
        $$("body").append(comp);
        comp.css({
            display: "block"
        }).find("[data-confirm-message]").html(message);
        const dismiss = _ => {
            comp.rClass("show").css({opacity: 0, transition: '.3s'});
            setTimeout(_ => comp.remove(), 400);
        }
        comp.find("[data-confirm-ok]").on("click", function (ev) {
            ev.preventDefault();
            dismiss();
            typeof handler === "function" ? handler() : null;
        });
        comp.find("[data-confirm-dismiss]").on("click", function (ev) {
            ev.preventDefault();
            dismiss();
        });
        setTimeout(_ => comp.aClass("show"));
    };


    const alert = (message) => {
        let comp = main.ui.get_component(".d-alert");
        $$("body").append(comp);
        comp.css({
            display: "block"
        }).find("[data-alert-message]").html(message);
        const dismiss = _ => {
            comp.rClass("show").css({opacity: 0, transition: '.3s'});
            setTimeout(_ => comp.remove(), 400);
        }
        comp.find("[data-alert-dismiss]").on("click", function (ev) {
            ev.preventDefault();
            dismiss();
        });
        setTimeout(_ => comp.aClass("show"));
    };

    const DialogLoader = (message) => {
        if (DialogLoader.loading) {
            let comp = $$(".d-loader");
            comp.rClass("show").css({opacity: 0, transition: '.3s'});
            setTimeout(_ => comp.remove(), 400);
            return;
        }
        let comp = main.ui.get_component(".d-loader");
        const dismiss = _ => {
            DialogLoader.loading = false;
            comp.rClass("show").css({opacity: 0, transition: '.3s'});
            setTimeout(_ => comp.remove(), 400);
        }


        $$("body").append(comp);
        comp.css({
            display: "block"
        }).find("[data-prog-message]").html(message || "");

        DialogLoader.loading = true;
        comp.find("[data-prog-dismiss]").on("click", function (ev) {
            ev.preventDefault();
            dismiss();
        });
        setTimeout(_ => comp.aClass("show"));
    };
    DialogLoader.loading = false;

    const Request = (config, handler) => {
        $.post({url: "/control-panel/request", data: config})
            .then(res => {
                typeof handler === "function" ? handler(res) : null;
            }).catch(err => {
            typeof handler === "function" ? handler({ok: 0, data: "Something went wrong"}) : null;
        });
    };
    main.init();


    $$(document).on("click", "[data-smv-component=db-config", function (ev) {
        ev.preventDefault()
        main.settings.db_config(this);

    });
    $$(document).on("click", "[data-smv-toggle=model-item", function (ev) {
        ev.preventDefault()
    });

    $$(document).on("click", ".field-option", function (ev) {
        ev.preventDefault();
        let s = $$(this);
        let drop = s.closest(".field-options");
        drop.rClass("show");
        s.closest(".field-content").find("[data-col-type]")
            .txt(s.txt().trim().toUpperCase());

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
    let lib = {
        init() {
            lib.dropDown()
        },
        dropDown() {
            $$(document).on("click", "[data-smv-toggle=dropdown]", function (ev) {
                ev.preventDefault();

                let drop = $$(this).next(), self = $$(this);

                let
                    params = self.params(), offset = self.offset();
                let top = params.height
                setTimeout(_ => drop.tClass("show"));

            }).on("click", function (ev) {
                $$(".dropdown-menu.show").rClass("show")
            });
        }
    }
    lib.init();

});
