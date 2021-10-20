/**
 * (c) VisualSearch GmbH <office@visualsearch.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the source code.
 */
(function () {
    if (typeof recommendsimilarproducts === 'undefined') {
        return;
    }

    function getSourceProductId() {
        var $input = $('#product_page_product_id');
        
        return ($input.length === 1) ? $input.val() : 0;
    }
    
    function sendActionData(action, $target) {
        var data = {action};

        if ($target.hasClass('accessories-block')) {
            data['id_product'] = getSourceProductId();
        } else {
            var $productMiniature = $target.hasClass('ajax_block_product') ?
                $target :
                $target.closest('.ajax_block_product');
            
            if ($productMiniature.length !== 1) {
                return;
            }

            var $productData = $productMiniature.find('.product-data');
            if ($productData.length !== 1) {
                return;
            }

            data['id_product'] = $productData.data('id_product');
            data['id_product_attribute'] = $productData.data('id_product_attribute');

            if (action === 'click') {
                data['id_source_product'] = getSourceProductId();
            }
        }

        $.post(recommendsimilarproducts.ajax_url, data);
    }

    function checkVisibility() {
        var $productAccessories = $('.accessories-block');
        if ($productAccessories.length !== 1) {
            return;
        }

        var $window = $(window);
        var wt = $window.scrollTop();
        var wh = $window.height();
        var wl = $window.scrollLeft();
        var ww = $window.width();

        if (!$productAccessories.data('viewed')) {
            var pah = $productAccessories.height();  
            var pat = $productAccessories.offset().top;
            
            if (((pat <= wt     ) && (pat + pah >  wt     )) ||
                ((pat >= wt     ) && (pat + pah <= wt + wh)) ||
                ((pat <  wt + wh) && (pat + pah >= wt + wh))) {
                sendActionData('block_view', $productAccessories);
                $productAccessories.data('viewed', true);
            }
        }
        
        $productAccessories.find('.ajax_block_product').each(function() {
            var $this = $(this);

            if ($this.data('viewed')) {
                return;
            }

            var eh = $this.height();  
            var et = $this.offset().top;
            var ew = $this.width();
            var el = $this.offset().left;

            if ((et >= wt) && (et + eh <= wt + wh) && (el >= wl) && (el + ew <= wl + ww)) {
                sendActionData('view', $this);
                $this.data('viewed', true);
            }
        });

        setTimeout(checkVisibility, 500);
    }
    
    $(document).ready(function() {
        checkVisibility();
    }).on('click', '.accessories-block .ajax_block_product a', function() {
        sendActionData('click', $(this));
    }).on('mousedown', '.accessories-block .ajax_block_product a', function(e1) {
        $(document).one('mouseup', '.accessories-block .ajax_block_product a', function(e2) {
            if ((e1.which === 2) && (e1.target === e2.target)) {
                sendActionData('click', $(this));
            }
        })
    });
}());
