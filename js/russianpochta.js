(function ($) {
    "use strict";
    $.storage = new $.store();
    $.russianpochta = {
        options: {},
        // last list view user has visited: {title: "...", hash: "..."}
        lastView: null,
        init: function (options) {
            var that = this;
            that.options = options;
            if (typeof ($.History) != "undefined") {
                $.History.bind(function () {
                    that.dispatch();
                });
            }
            $.wa.errorHandler = function (xhr) {
                if ((xhr.status === 403) || (xhr.status === 404)) {
                    var text = $(xhr.responseText);
                    if (text.find('.dialog-content').length) {
                        text = $('<div class="block double-padded"></div>').append(text.find('.dialog-content *'));
                    } else {
                        text = $('<div class="block double-padded"></div>').append(text.find(':not(style)'));
                    }
                    $("#russianpochta-content").empty().append(text);
                    that.onPageNotFound();
                    return false;
                }
                return true;
            };
            var hash = this.getHash();
            if (hash === '#/' || !hash) {
                this.dispatch();
            } else {
                $.wa.setHash(hash);
            }

        },
        // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
        // *   Dispatch-related
        // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

        // if this is > 0 then this.dispatch() decrements it and ignores a call
        skipDispatch: 0,
        /** Cancel the next n automatic dispatches when window.location.hash changes */
        stopDispatch: function (n) {
            this.skipDispatch = n;
        },
        /** Force reload current hash-based 'page'. */
        redispatch: function () {
            this.currentHash = null;
            this.dispatch();
        },
        dispatch: function (hash) {
            if (this.skipDispatch > 0) {
                this.skipDispatch--;
                return false;
            }

            if (hash === undefined || hash === null) {
                hash = this.getHash();
            }
            if (this.currentHash == hash) {
                return;
            }

            this.currentHash = hash;
            hash = hash.replace('#/', '');
            if (hash) {
                hash = hash.split('/');
                if (hash[0]) {
                    var actionName = "";
                    var attrMarker = hash.length;
                    for (var i = 0; i < hash.length; i++) {
                        var h = hash[i];
                        if (i < 2) {
                            if (i === 0) {
                                actionName = h;
                            } else if (parseInt(h, 10) != h && h.indexOf('=') == -1) {
                                actionName += h.substr(0, 1).toUpperCase() + h.substr(1);
                            } else {
                                attrMarker = i;
                                break;
                            }
                        } else {
                            attrMarker = i;
                            break;
                        }
                    }
                    var attr = hash.slice(attrMarker);
                    this.preExecute(actionName);
                    if (typeof (this[actionName + 'Action']) == 'function') {
                        $.shop.trace('$.russianpochta.dispatch', [actionName + 'Action', attr]);
                        this[actionName + 'Action'].apply(this, attr);
                    } else {
                        $.shop.error('Invalid action name:', actionName + 'Action');
                    }
                    this.postExecute(actionName);
                } else {
                    this.preExecute();
                    this.defaultAction();
                    this.postExecute();
                }
            } else {
                this.preExecute();
                this.defaultAction();
                this.postExecute();
            }


        },
        preExecute: function (actionName, attr) {
        },
        postExecute: function (actionName, attr) {
            this.actionName = actionName;
        },
        defaultAction: function () {
            var self = this;
            this.load('?plugin=russianpochta&action=orders', function () {
                self.initSearch();
                self.initShopOrdersDialog('orders');
            });
        },
        dispatchAction: function () {
            var self = this;
            this.load('?plugin=russianpochta&action=dispatch', function () {
                self.initBatchOrders('dispatch');
                self.initMoveToArchive();
                self.initOrderSearchDialog();
                self.initDeleteFromBatchDialog();
                self.initShopOrdersDialog('dispatch');
                self.initDatePicker();
            });
        },
        archiveAction: function () {
            var self = this;
            this.load('?plugin=russianpochta&action=archive', function () {
                self.initBatchOrders('archive');
                self.initRevertBatchFormArchive();
            });
        },
        /** Current hash */
        getHash: function () {
            return this.cleanHash();
        },
        /** Make sure hash has a # in the begining and exactly one / at the end.
         * For empty hashes (including #, #/, #// etc.) return an empty string.
         * Otherwise, return the cleaned hash.
         * When hash is not specified, current hash is used. */
        cleanHash: function (hash) {
            if (typeof hash == 'undefined') {
                hash = window.location.hash.toString();
            }

            if (!hash.length) {
                hash = '' + hash;
            }
            while (hash.length > 0 && hash[hash.length - 1] === '/') {
                hash = hash.substr(0, hash.length - 1);
            }
            hash += '/';
            if (hash[0] != '#') {
                if (hash[0] != '/') {
                    hash = '/' + hash;
                }
                hash = '#' + hash;
            } else if (hash[1] && hash[1] != '/') {
                hash = '#/' + hash.substr(1);
            }

            if (hash == '#/') {
                return '';
            }

            return hash;
        },
        load: function (url, options, fn) {
            if (typeof options === 'function') {
                fn = options;
                options = {};
            } else {
                options = options || {};
            }
            var r = Math.random();
            this.random = r;
            var self = this;
            (options.content || $("#russianpochta-content")).html('<div class="block triple-padded"><i class="icon16 loading"></i>Loading...</div>');
            return  $.get(url, function (result) {
                if ((typeof options.check === 'undefined' || options.check) && self.random != r) {
                    // too late: user clicked something else.
                    return;
                }

                (options.content || $("#russianpochta-content")).removeClass('bordered-left').html(result);
                if (typeof fn === 'function') {
                    fn.call(this);
                }

            });
        },
        onPageNotFound: function () {
            //this.defaultAction();
        },
        initSearch: function () {
            var self = this;
            $('#russianpochta-search-order-form').submit(function () {
                self.load('?plugin=russianpochta&action=orders&' + $(this).serialize(), function () {
                    self.initSearch();
                    self.initOrderDialog('order');
                    self.initDeleteOrder();
                    self.initCreateBatch();
                    self.initMoveOrderToBatchDialog();
                });
            });
        },
        initShopOrdersDialog: function (param) {
            $('.shop-orders-dialog-btn').click(function () {
                if ($('#russianpochta-dialog .dialog-content-indent').length) {
                    $('#russianpochta-dialog .dialog-content-indent').html('<i class="icon16 loading"></i>');
                } else {
                    $('#russianpochta-dialog').html('<i class="icon16 loading"></i>');
                }
                var dialog = $('#russianpochta-dialog').waDialog({
                    disableButtonsOnSubmit: false,
                    buttons: $('<input type="submit" class="button green" value="Закрыть">').click(function () {
                        dialog.trigger('close');
                    }),
                    onSubmit: function (d) {
                        return false;
                    }
                });
                var url = '?plugin=russianpochta&module=order&action=shopOrdersDialog';
                if (param == 'dispatch') {
                    url += '&batch_name=' + $(this).closest('tr').data('batch-name');
                }


                $.ajax({
                    type: 'GET',
                    url: url,
                    dataType: 'html',
                    success: function (html) {
                        if ($(html).find('.dialog-window').length) {
                            $('#russianpochta-dialog').html(html);
                        } else {
                            $('#russianpochta-dialog .dialog-content-indent').html(html);
                        }
                    }
                });
                return false;
            });
        },
        initOrderDialog: function (param) {
            $('.russianpochta-open-dialog').click(function () {
                if ($('#russianpochta-dialog .dialog-content-indent').length) {
                    $('#russianpochta-dialog .dialog-content-indent').html('<i class="icon16 loading"></i>');
                } else {
                    $('#russianpochta-dialog').html('<i class="icon16 loading"></i>');
                }
                var dialog = $('#russianpochta-dialog').waDialog({
                    disableButtonsOnSubmit: false,
                    buttons: $('<input type="submit" class="button green" value="Закрыть">').click(function () {
                        dialog.trigger('close');
                    }),
                    onSubmit: function (d) {
                        return false;
                    }
                });
                var url = '?plugin=russianpochta&module=order&action=dialog';
                if (param == 'order') {
                    url += '&order_num=' + $(this).closest('tr').data('order-num');
                } else if (param == 'batch') {
                    url += '&order_id=' + $(this).closest('tr').data('order-id');
                }
                $.ajax({
                    type: 'GET',
                    url: url,
                    dataType: 'html',
                    success: function (html) {
                        if ($(html).find('.dialog-window').length) {
                            $('#russianpochta-dialog').html(html);
                        } else {
                            $('#russianpochta-dialog .dialog-content-indent').html(html);
                        }
                    }
                });
                return false;
            });
        },
        initDeleteOrder: function () {
            $('.delete-order').click(function () {
                var tr = $(this).closest('tr');
                $.ajax({
                    url: '?plugin=russianpochta&module=order&action=delete',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        order_id: tr.data('order-id')
                    },
                    success: function (data, textStatus) {
                        if (data.status == 'ok') {
                            //form.closest('.dialog').trigger('close');
                        } else {
                            alert(data.errors.join(', '));
                        }
                    }, error: function (jqXHR, textStatus, errorThrown) {
                        alert(jqXHR.responseText);
                    }
                });
                return false;
            });
        },
        initCreateBatch: function () {
            $('.сreate-batch').click(function () {
                var tr = $(this).closest('tr');
                $.ajax({
                    url: '?plugin=russianpochta&module=batch&action=create',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        order_id: tr.data('order-id')
                    },
                    success: function (data, textStatus) {
                        if (data.status == 'ok') {
                            tr.remove();
                        } else {
                            alert(data.errors.join(', '));
                        }
                    }, error: function (jqXHR, textStatus, errorThrown) {
                        alert(jqXHR.responseText);
                    }
                });
                return false;
            });
        },
        initMoveOrderToBatchDialog: function () {
            $('.move-order-to-batch-dialog').click(function () {
                var tr = $(this).closest('tr');
                if ($('#russianpochta-dialog .dialog-content-indent').length) {
                    $('#russianpochta-dialog .dialog-content-indent').html('<i class="icon16 loading"></i>');
                } else {
                    $('#russianpochta-dialog').html('<i class="icon16 loading"></i>');
                }
                var dialog = $('#russianpochta-dialog').waDialog({
                    disableButtonsOnSubmit: false,
                    buttons: $('<input type="submit" class="button green" value="Закрыть">').click(function () {
                        dialog.trigger('close');
                    }),
                    onSubmit: function (d) {
                        return false;
                    }
                });
                $.ajax({
                    type: 'GET',
                    url: '?plugin=russianpochta&module=order&action=moveToBatchDialog&order_id',
                    dataType: 'html',
                    data: {
                        order_id: tr.data('order-id')
                    },
                    success: function (html) {
                        if ($(html).find('.dialog-window').length) {
                            $('#russianpochta-dialog').html(html);
                        } else {
                            $('#russianpochta-dialog .dialog-content-indent').html(html);
                        }
                    }
                });
                return false;
            });
        },
        initOrderSearchDialog: function () {
            var self = this;
            $('.order-search-dialog-btn').click(function () {
                var tr = $(this).closest('tr');
                var batch_name = tr.data('batch-name');
                if ($('#russianpochta-dialog .dialog-content-indent').length) {
                    $('#russianpochta-dialog .dialog-content-indent').html('<i class="icon16 loading"></i>');
                } else {
                    $('#russianpochta-dialog').html('<i class="icon16 loading"></i>');
                }
                var dialog = $('#russianpochta-dialog').waDialog({
                    disableButtonsOnSubmit: false,
                    buttons: $('<input type="submit" class="button green" value="Закрыть">').click(function () {
                        dialog.trigger('close');
                    }),
                    onSubmit: function (d) {
                        return false;
                    }
                });
                $.ajax({
                    type: 'GET',
                    url: '?plugin=russianpochta&module=order&action=searchDialog',
                    dataType: 'html',
                    data: {
                        batch_name: batch_name
                    },
                    success: function (html) {
                        if ($(html).find('.dialog-window').length) {
                            $('#russianpochta-dialog').html(html);
                        } else {
                            $('#russianpochta-dialog .dialog-content-indent').html(html);
                        }
                    }
                });
                return false;
            });
        },
        initBatchOrders: function (action_name) {
            var self = this;
            $('.batch-orders-btn').click(function () {
                var tr = $(this).closest('tr');
                var batch_name = tr.data('batch-name');
                if (tr.next('.batch-orders[data-batch-name=' + batch_name + ']').length) {
                    tr.next('.batch-orders[data-batch-name=' + batch_name + ']').slideToggle('fast');
                } else {
                    $.ajax({
                        url: '?plugin=russianpochta&module=batch&action=orders&action_name=' + action_name,
                        type: 'GET',
                        dataType: 'html',
                        data: {
                            batch_name: batch_name
                        },
                        success: function (html) {
                            var batch_orders = $('<tr class="batch-orders" data-batch-name="' + batch_name + '"><td colspan="7"></td></tr>');
                            tr.after(batch_orders);
                            batch_orders.find('td').append(html);
                            self.initOrderDialog('batch');
                            self.initRevertOrderFormBatch();
                        }
                    });
                }

                return false;
            });
        },
        initMoveToArchive: function () {
            $('.move-to-archive-btn').click(function () {
                var tr = $(this).closest('tr');
                var batch_name = tr.data('batch-name');
                $.ajax({
                    url: '?plugin=russianpochta&module=batch&action=moveToArchive',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        batch_name: batch_name
                    },
                    success: function (data, textStatus) {
                        if (data.status == 'ok') {
                            tr.remove();
                            if (tr.next('.batch-orders[data-batch-name=' + batch_name + ']').length) {
                                tr.next('.batch-orders[data-batch-name=' + batch_name + ']').remove();
                            }
                        } else {
                            alert(data.errors.join(', '));
                        }
                    }, error: function (jqXHR, textStatus, errorThrown) {
                        alert(jqXHR.responseText);
                    }
                });
                return false;
            });
        },
        initDeleteFromBatchDialog: function () {
            var self = this;
            $('.delete-from-batch-dialog-btn').click(function () {
                var tr = $(this).closest('tr');
                var batch_name = tr.data('batch-name');
                if ($('#russianpochta-dialog .dialog-content-indent').length) {
                    $('#russianpochta-dialog .dialog-content-indent').html('<i class="icon16 loading"></i>');
                } else {
                    $('#russianpochta-dialog').html('<i class="icon16 loading"></i>');
                }
                var dialog = $('#russianpochta-dialog').waDialog({
                    disableButtonsOnSubmit: false,
                    buttons: $('<input type="submit" class="button green" value="Закрыть">').click(function () {
                        dialog.trigger('close');
                    }),
                    onSubmit: function (d) {
                        return false;
                    }
                });
                $.ajax({
                    type: 'GET',
                    url: '?plugin=russianpochta&module=batch&action=deleteDialog',
                    dataType: 'html',
                    data: {
                        batch_name: batch_name
                    },
                    success: function (html) {
                        if ($(html).find('.dialog-window').length) {
                            $('#russianpochta-dialog').html(html);
                        } else {
                            $('#russianpochta-dialog .dialog-content-indent').html(html);
                        }
                    }
                });
                return false;
            });
        },
        initRevertOrderFormBatch: function () {
            $('.revert-order-form-batch').click(function () {
                var tr = $(this).closest('tr');
                $.ajax({
                    url: '?plugin=russianpochta&module=order&action=revert',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        order_id: tr.data('order-id')
                    },
                    success: function (data, textStatus) {
                        if (data.status == 'ok') {
                            window.location.reload();
                        } else {
                            alert(data.errors.join(', '));
                        }
                    }, error: function (jqXHR, textStatus, errorThrown) {
                        alert(jqXHR.responseText);
                    }
                });
                return false;
            });
        },
        initRevertBatchFormArchive: function () {
            $('.revert-batch-form-archive').click(function () {
                var tr = $(this).closest('tr');
                $.ajax({
                    url: '?plugin=russianpochta&module=batch&action=revert',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        batch_name: tr.data('batch-name')
                    },
                    success: function (data, textStatus) {
                        if (data.status == 'ok') {
                            window.location.reload();
                        } else {
                            alert(data.errors.join(', '));
                        }
                    }, error: function (jqXHR, textStatus, errorThrown) {
                        alert(jqXHR.responseText);
                    }
                });
                return false;
            });
        },
        initDatePicker: function () {
            $('.batch-status-date').datepicker({
                dateFormat: 'dd.mm.yy'
            });
            $('.batch-status-date').change(function () {
                var tr = $(this).closest('tr');
                var loading = $('<i class="icon16 loading"></i>');
                $(this).after(loading);
                $.ajax({
                    url: '?plugin=russianpochta&module=batch&action=setSendingDate',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        batch_name: tr.data('batch-name'),
                        date: $(this).val()
                    },
                    success: function (data, textStatus) {
                        if (data.status == 'ok') {
                            var yes_icon = $('<i class="icon16 yes"></i>');
                            loading.replaceWith(yes_icon);
                            setTimeout(function () {
                                yes_icon.remove();
                            }, 3000);
                        } else {
                            loading.remove();
                            alert(data.errors.join(', '));
                        }
                    }, error: function (jqXHR, textStatus, errorThrown) {
                        loading.remove();
                        alert(jqXHR.responseText);
                    }
                });
            });
        }

    };
})(jQuery);