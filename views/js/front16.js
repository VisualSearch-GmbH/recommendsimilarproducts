/**
 * (c) VisualSearch GmbH <office@visualsearch.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the source code.
 */
(function () {
    if (typeof recommendsimilarproducts === 'undefined') {
        return;
    }

    function sendActionData(action, $target) {
        var data = {action};

        if ($target.hasClass('accessories-block')) {
            data['id_product'] = recommendsimilarproducts.id_source_product;
        } else {
            var $productData = $target.find('.product-data');
            if ($productData.length !== 1) {
                return;
            }

            data['id_product'] = $productData.data('id_product');
            data['id_product_attribute'] = $productData.data('id_product_attribute');
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
        
        var paramsTemplate = 'rsp=1&id_source_product=' + recommendsimilarproducts.id_source_product +
            '&id_target_attribute=';

        $('.accessories-block .ajax_block_product a').each(function() {
            var $link = $(this);
            var url = $link.attr('href');
            var anchor = '';
            var $productData = $link.closest('.ajax_block_product').find('.product-data');
            var params = paramsTemplate + ($productData.length ? $productData.data('id_product_attribute') : 0);
            
            if (url.indexOf('#') !== -1) {
                anchor = url.substring(url.indexOf('#'), url.length);
                url = url.substring(0, url.indexOf('#'));
            }
            
            $link.attr('href', url + (url.indexOf('?') === -1 ? '?' : '&') + params + anchor);
        });
    });
}());
