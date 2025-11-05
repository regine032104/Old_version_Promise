// Simple AJAX Cart integration using jQuery
(function () {
    function showMessage(message, type = 'info') {
        var el = document.getElementById('ajax-cart-message');
        if (!el) {
            el = document.createElement('div');
            el.id = 'ajax-cart-message';
            el.style.position = 'fixed';
            el.style.right = '20px';
            el.style.top = '20px';
            el.style.zIndex = '9999';
            document.body.appendChild(el);
        }
        el.textContent = message;
        el.style.padding = '10px 14px';
        el.style.borderRadius = '8px';
        el.style.color = '#fff';
        el.style.background = type === 'error' ? '#e11' : '#059669';
        el.style.opacity = '1';
        setTimeout(function () { el.style.opacity = '0'; }, 3000);
    }

    function updateNavCount(count) {
        var el = document.getElementById('nav-cart-count');
        if (el) el.textContent = count;
    }

    function refreshBadge() {
        if (window.PromiseUI && typeof window.PromiseUI.refreshCartCount === 'function') {
            try { window.PromiseUI.refreshCartCount(); } catch (e) { /* ignore */ }
            return;
        }
        // fallback: do nothing (updateNavCount is already called where possible)
    }

    $(document).ready(function () {
    // Intercept add-to-cart forms (forms posting to cart.php or cart_api.php)
    $('form[action="cart.php"], form[action="../backend/cart_api.php"]').on('submit', function (e) {
            e.preventDefault();
            var $form = $(this);

            // If this is the update cart form (has input name 'update'), perform bulk update
            if ($form.find('input[name="update"]').length > 0) {
                var data = { action: 'bulk_update' };
                $form.find('input[name^="quantity-"]').each(function () {
                    data[$(this).attr('name')] = $(this).val();
                });

                $.ajax({
                    url: '../backend/cart_api.php',
                    method: 'POST',
                    data: data,
                    dataType: 'json'
                }).done(function (resp) {
                    if (resp.success) {
                        // Update quantities and row/summary totals in-place using server response
                        try {
                            var updated = resp.updated || {};
                            var subtotal = 0;
                            Object.keys(updated).forEach(function (pid) {
                                var q = parseInt(updated[pid], 10) || 0;
                                var $input = $('[name="quantity-' + pid + '"]');
                                var $row = $('tr[data-product-id="' + pid + '"]');
                                if ($input.length) $input.val(q);
                                if (q <= 0) {
                                    // remove row
                                    if ($row.length) $row.remove();
                                    return;
                                }
                                // read unit price from data attribute on price cell
                                var $priceCell = $row.find('td[data-unit-price]');
                                var unit = 0;
                                if ($priceCell.length) unit = parseFloat($priceCell.attr('data-unit-price')) || 0;
                                var rowTotal = unit * q;
                                // write formatted total
                                var $rowTotal = $('#row-total-' + pid);
                                if ($rowTotal.length) $rowTotal.text(formatPrice(rowTotal));
                                subtotal += rowTotal;
                            });

                            // Recalculate subtotal by summing remaining row-totals in case some rows weren't in updated
                            var sats = 0;
                            $('.row-total').each(function () { var v = parseFloat($(this).text().replace(/[^0-9.-]+/g, '')) || 0; sats += v; });
                            if (sats > 0) subtotal = sats;

                            // Update summary
                            var $sub = document.getElementById('cart-subtotal');
                            var $tot = document.getElementById('cart-total');
                            if ($sub) $sub.textContent = formatPrice(subtotal);
                            if ($tot) $tot.textContent = formatPrice(subtotal);

                            // Show messages
                            if (resp.errors && resp.errors.length) {
                                showMessage('Some quantities were adjusted to available stock', 'error');
                            } else {
                                showMessage('Cart updated', 'success');
                            }

                            // Update badge
                            if (resp.cartCount !== undefined) updateNavCount(resp.cartCount);
                            refreshBadge();
                        } catch (e) {
                            // fallback to reload if anything unexpected happened
                            setTimeout(function () { window.location.reload(); }, 300);
                        }
                    } else {
                        showMessage(resp.message || 'Failed to update cart', 'error');
                    }
                }).fail(function () {
                    showMessage('Network error', 'error');
                });

                return;
            }

            // Otherwise treat as add-to-cart
            var pid = $form.find('input[name="product_id"]').val();
            var qty = $form.find('input[name="quantity"]').val() || 1;

            $.ajax({
                url: '../backend/cart_api.php',
                method: 'POST',
                data: { action: 'add', product_id: pid, quantity: qty },
                dataType: 'json'
            }).done(function (resp) {
                if (resp.success) {
                    // refresh from server for authoritative count
                    refreshBadge();
                    showMessage(resp.message || 'Added to cart', 'success');
                } else {
                    var msg = resp.message || 'Could not add to cart';
                    if (resp.available !== undefined) msg += ' (available: ' + resp.available + ')';
                    showMessage(msg, 'error');
                }
            }).fail(function () {
                showMessage('Network error', 'error');
            });
        });

        // Optional: intercept remove links that use cart.php?remove=
        // Bind remove links (new markup uses .remove-item with data-product-id)
        $(document).on('click', 'a.remove-item', function (e) {
            e.preventDefault();
            var pid = $(this).data('product-id');
            if (!pid) return;
            $.ajax({
                url: '../backend/cart_api.php',
                method: 'POST',
                data: { action: 'remove', product_id: pid },
                dataType: 'json'
            }).done(function (resp) {
                if (resp.success) {
                    // remove row from cart if present
                    var $row = $('tr[data-product-id="' + pid + '"]');
                    if ($row.length) $row.remove();
                    // recompute subtotal
                    var subtotal = 0;
                    $('.row-total').each(function () { var v = parseFloat($(this).text().replace(/[^0-9.-]+/g, '')) || 0; subtotal += v; });
                    var $sub = document.getElementById('cart-subtotal');
                    var $tot = document.getElementById('cart-total');
                    if ($sub) $sub.textContent = formatPrice(subtotal);
                    if ($tot) $tot.textContent = formatPrice(subtotal);

                    refreshBadge();
                    showMessage('Removed from cart', 'success');
                } else {
                    showMessage('Could not remove item', 'error');
                }
            }).fail(function () { showMessage('Network error', 'error'); });
        });
    });
    
    // Helper: simple currency formatter to match server formatting ($x.xx)
    function formatPrice(n) {
        var num = Number(n) || 0;
        return '$' + num.toFixed(2);
    }
})();
