
class VisideaMagento {

    constructor(visidea, shop, public_token)
    {

        // Initialize fields
        this.shop = shop;
        this.public_token = public_token;
        this.visidea = {};
        this.hotpoints = [];
        this.resizeFactor = 1;
        // this.whenAvailable('Visidea', function() {

        // Initialize visidea
        this.visidea = visidea;
        // console.log(this.visidea)

        // Load necessary scripts and styles
        this.loadCss(
            `
            .visidea {
                margin: 2em 0;
            }
            .visidea h2 {
                text-align: center;
            }
            .visidea a {
                outline: 0;
                color:black;
            }
            .visidea p {
                margin:0;
            }
            .visidea .visidea__product {
                padding: 0px 0.5rem;
                text-align: center;
                outline: 0;
                line-height: 1.5;
            }
            .visidea .visidea__product-heading, .visidea .visidea__product-brand {
                margin: 0;
            }
            .visidea .product-item-details {
                text-align: center;
            }
            .visidea img.visidea__product-image {
                max-width: 100%;
                max-height: 100%;
            }
            .visidea .slick-next:before, .visidea .slick-prev:before {
                color: #000!important;
                opacity: .25;
                padding-left:0;
                padding-right:0;
            }
            .slick-slide img {
                max-height: 100%;
            }
            .slick-next, .slick-prev {
                z-index: 9999;
                display: inline-block!important;
                overflow: hidden;
            }
            .slick-prev {
                left: 20px;
                float: left;
                margin: 0;
                text-indent: 0;
            }
            .slick-next {
                right: 20px;
                float: left;
                margin: 0;
                text-indent: 0;
            }
            .woocommerce.widget_product_search {
                white-space: nowrap;
            }
            .visidea-visualsearch-icon {
                display: inline-block;
                float:right;
                width:28px;
                margin-left: 10px;
            }
            .visidea-visualsearch-icon img {
                // margin-top: 3px;
                width:28px;
                height:28px;
            }
            .woocommerce-product-search {
                display: inline-block;
            }
            .visidea-visualsearch {
                display:none;
                background:white;
                position:absolute;
                z-index: 99999;
                top:10px;
                bottom:10px;
                right:10px;
                left:10px;
            }
            .visidea-visualsearch p {
                margin:0;
            }
            .visidea-visualsearch a {
                color:black;
            }
            .visidea-visualsearch__exit {
                float:right;
                background-image:url("https://cdn.visidea.ai/imgs/icons/svg/visidea_cancelcircle.svg");
                background-color:#fff;
                width:30px;
                height:30px;
                margin:10px;
            }
            .visidea-visualsearch__exit:hover {
                cursor: pointer;
            }
            .visidea-visualsearch__upload {
                border: dashed 2px #000;
                margin: 15px;
                padding: 15px;
                text-align:center;
            }
            .visidea-visualsearch__upload-photo {
                background:url("https://cdn.visidea.ai/imgs/icons/svg/visidea_camera.svg");
                width:40px;
                height:40px;
                margin:auto;
                margin-botton:15px;
            }
            .visidea-visualsearch__upload-photo:hover {
                cursor: pointer;
            }
            .visidea-visualsearch__upload-input {
                width: 0px;
                height: 0px;
                overflow: hidden;
                display: none;
            }
            .visidea-visualsearch__container {
                display: block;
                margin: 15px;
                margin-top: 0px;
            }
            .visidea-visualsearch__nav {
                display: block;
                width: 100%;
                margin-right: 15px;
                margin-bottom: 15px;
            }
            .visidea-visualsearch__nav-content {
                position: relative;
                display: block;
                width: 150px;
                margin: auto;
            }
            .visidea-visualsearch__content {
                display:flex;
                flex-direction: column;
                width:100%;
                overflow:auto;
            }
            .visidea-visualsearch__upload-image {
                width: 100%!important;
            }
            #visidea-visualsearch__upload-canvas {
                position: absolute;
                top: 0;
                left: 0;
                margin-top: 0;
                margin-left: 0;
            }
            #visidea-visualsearch__upload-canvas-crop {
                width: 800px;
                height: 800px;
                display:none;
            }
            .visidea-visualsearch .visidea__product {
                width:50%;
                float:left;
                margin-bottom:15px;
                line-height: 1.5;
            }
            .visidea-visualsearch .visidea__product a {
                display: flex;
                justify-content: center;
            }
            .visidea-visualsearch img.visidea__product-image {
                max-width: 100%;
            }
            .visidea-visualsearch .visidea__product-caption {
                text-align: center;
            }
            .visidea-visualsearch .visidea__product:nth-child(2n+1){
                clear:left;
            }
            @media only screen and (min-width: 768px) {
                .visidea-visualsearch__container {
                    display: flex;
                }
                .visidea-visualsearch__upload-photo {
                    width:150px;
                    height:150px;
                }
                .visidea-visualsearch__nav {
                    display: flex;
                    flex-direction: column;
                    width: 220px;
                }
                .visidea-visualsearch__nav-content {
                    display: inline-block;
                    width: 220px;
                    margin: 0;
                }
            }
            @media only screen and (min-width: 1024px) {
                .visidea-visualsearch .visidea__product {
                    width:25%;
                }
                .visidea-visualsearch .visidea__product:nth-child(2n+1){
                    clear:none;
                }
                .visidea-visualsearch .visidea__product:nth-child(4n+1){
                    clear:left;
                }
                .visidea-visualsearch__upload {
                    margin: 50px;
                    padding: 50px;
                }
                .visidea-visualsearch__container {
                    margin: 50px;
                }
            }
            `
        );

        this.initializeVisualSearch();

        // }.bind(this));

    }

    setUser(user_id)
    {
        var old_user_id = localStorage.getItem("Visidea.user");
        if (old_user_id == null) {
            if (user_id == 0) {
                user_id = this.visidea.uuidv4();
            }    
            localStorage.setItem("Visidea.user", user_id);
        } else {
            if (user_id == 0) {
                user_id = old_user_id
            }
        }
        if (old_user_id != user_id && old_user_id != null) {
            localStorage.setItem("Visidea.user", user_id);
            this.visidea.merge_users(old_user_id, user_id);
        }
        return user_id;
    }

    async initializeVisualSearch()
    {
        // Wait for 'visidea.conf' to exist
        while (!this.visidea.conf) {
            //console.log('wait');
            await new Promise(r => setTimeout(r,10))
        }

        if (!this.visidea.conf.has_visualseach)
            return;

        var html = '<div class="visidea-visualsearch-icon"><a href="javascript:void(0)" onclick="visideaMagento.showVisualSearch()"><img src="https://cdn.visidea.ai/imgs/icons/svg/visidea_camera.svg"></a></div>';

        if (this.visidea.conf.visualsearch && this.visidea.conf.visualsearch.show_after != '')
            jQuery(elem.show_after).after(html);
        else
            jQuery('.block-search').before(html);

        var html = '<div class="visidea-visualsearch" id="visidea-visualsearch">';
        html = html + '<a class="visidea-visualsearch__exit" onclick="visideaMagento.hideVisualSearch()"></a>';
        html = html + '<div class="visidea-visualsearch__upload" onclick="jQuery(\'.visidea-visualsearch__upload-input\').trigger(\'click\');" ondrop="visideaMagento.dropHandler(event);" ondragover="visideaMagento.dragOverHandler(event);"><div class="visidea-visualsearch__upload-photo"></div>Upload or snap a photo</div>';
        html = html + '<input class="visidea-visualsearch__upload-input" type="file" id="file" accept="image/*" onChange="visideaMagento.uploadFile(event)"/>';
        html = html + '<div class="visidea-visualsearch__container"><div class="visidea-visualsearch__nav"><div class="visidea-visualsearch__nav-content">';
        html = html + '<img id="visidea-visualsearch__upload-image" class="visidea-visualsearch__upload-image" src="" />';
        html = html + '<canvas id="visidea-visualsearch__upload-canvas"/>';
        html = html + '</div></div>';
        html = html + '<div class="visidea-visualsearch__content"><div class="visidea-visualsearch__content-hook"></div></div></div>';
        html = html + '<canvas id="visidea-visualsearch__upload-canvas-crop"/>';
        html = html + '</div>';

        jQuery('body').append(html);
        this.bindCanvasEvent();
        this.bindResizeEvent();

        this.recreateSession();

    }

    dragOverHandler(ev)
    {
        // Prevent default behavior (Prevent file from being opened)
        ev.preventDefault();
    }
    
    dropHandler(ev)
    {
        // Prevent default behavior (Prevent file from being opened)
        ev.preventDefault();
      
        var self = this;
    
        if (ev.dataTransfer.items) {
          // Use DataTransferItemList interface to access the file(s)
          [...ev.dataTransfer.items].forEach((item, i) => {
            // If dropped items aren't files, reject them
            if (item.kind === 'file') {
              const file = item.getAsFile();
              // console.log(`1 file[${i}].name = ${file.name}`);
              var url = URL.createObjectURL(file);
              jQuery('.visidea-visualsearch__upload-image').attr('src', url);
              this.getBase64(file).then(
                jpg => {
                    sessionStorage.setItem('visidea.image', jpg);
                    this.visidea.visualsearch(jpg, function(hotpoints){
                        self.hotpoints = hotpoints.detections;
                        self.drawHotpoints(self.hotpoints);
                        if (self.hotpoints.length)
                            self.selectHotpoint(self.hotpoints[0]);  
                    });
                }
              );
            }
          });
        } else {
          // Use DataTransfer interface to access the file(s)
          [...ev.dataTransfer.files].forEach((file, i) => {
            // console.log(`2 file[${i}].name = ${file.name}`);
            var url = URL.createObjectURL(file);
            jQuery('.visidea-visualsearch__upload-image').attr('src', url);
            this.getBase64(file).then(
              jpg => {
                  sessionStorage.setItem('visidea.image', jpg);
                  this.visidea.visualsearch(jpg, function(hotpoints){
                      self.hotpoints = hotpoints.detections;
                      self.drawHotpoints(self.hotpoints);
                      if (self.hotpoints.length)
                          self.selectHotpoint(self.hotpoints[0]);
                  });
              }
            );
          });
        }
    }
    

    showVisualSearch()
    {
        jQuery('.visidea-visualsearch').css('display','block');
        jQuery('html').scrollTop(0);
        this.fixContentHeight();
        jQuery('body').css('overflow-y','hidden');
    }

    fixContentHeight()
    {
        var width = jQuery('html').width();
        var height = jQuery('.visidea-visualsearch').height();
        var topheight = jQuery('.visidea-visualsearch__upload').height();
        var contentheight = height-topheight-210;
        if (width >= 768 && width < 1024) {
            contentheight += 120;
        }
        if (width < 768) {
            var navheight = jQuery('.visidea-visualsearch__nav').height();
            contentheight -= navheight;
            contentheight += 110;
        }
        jQuery('.visidea-visualsearch__content').css('height',contentheight+'px');
    }

    hideVisualSearch()
    {
        jQuery('.visidea-visualsearch').css('display','none');
        jQuery('body').css('overflow-y','auto');

        const url = new URL(window.location);
        url.searchParams.delete('visidea');
        url.searchParams.delete('visideaitem');
        window.history.pushState({}, '', url);
    }

    getBase64(file)
    {
        return new Promise(
            (resolve, reject) => 
            {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = () => resolve(reader.result);
                reader.onerror = error => reject(error);
            }
        );
    }

    uploadFile(event)
    {
        var self = this;
        var file = event.target.files[0];
        if (file) {
            var url = URL.createObjectURL(file);
            jQuery('.visidea-visualsearch__upload-image').attr('src', url);
            this.getBase64(file).then(
                jpg => 
                {
                    sessionStorage.setItem('visidea.image', jpg);
                    this.visidea.visualsearch(
                        jpg, function (hotpoints) {
                            self.hotpoints = hotpoints.detections;
                            self.drawHotpoints(self.hotpoints);
                            if (self.hotpoints.length)
                                self.selectHotpoint(self.hotpoints[0]);
                        }
                    );
                }
            );
        }
    }

    bindCanvasEvent()
    {
        var self = this;
        jQuery('#visidea-visualsearch__upload-canvas').click(
            function (event) {
                var elemLeft = jQuery('#visidea-visualsearch__upload-canvas').offset().left,
                    elemTop = jQuery('#visidea-visualsearch__upload-canvas').offset().top;

                var x = event.pageX - elemLeft,
                    y = event.pageY - elemTop;

                var image = document.getElementById("visidea-visualsearch__upload-image");
                var image_width = image.width;
                var image_height = image.height;

                // Collision detection between clicked offset and element.
                self.hotpoints.forEach(
                    function (element) {
                        var top = (element.ycenter - element.height / 2) * self.resizeFactor;
                        var left = (element.xcenter - element.width / 2) * self.resizeFactor;
                        var height = element.height * self.resizeFactor;
                        var width = element.width * self.resizeFactor;
                        var centerx = left + width/2;
                        var centery = top + height/2;
                        // if (y > top && y < top + height
                        //     && x > left && x < left + width) {
                        if (y > centery-20 && y < centery+20 && x > centerx-20 && x < centerx+20) {
                            // alert('clicked an element');
                            self.selectHotpoint(element);
                        }
                    }
                );
            }
        );
    }

    bindResizeEvent()
    {
        var self = this;
        jQuery(window).resize(
            function () {
                self.fixContentHeight();
            }
        );
    }

    recreateSession()
    {
        var self = this;
        var queryString = window.location.search;
        var urlParams = new URLSearchParams(queryString);
        var visidea = urlParams.get('visidea');
        var visideaitem = urlParams.get('visideaitem');
        if (visidea == 'visualsearch') {
            var jpg = sessionStorage.getItem('visidea.image');
            this.showVisualSearch();
            jQuery('.visidea-visualsearch__upload-image').attr('src', jpg);
            var visideaitem_center = visideaitem.split(',');

            this.visidea.visualsearch(
                jpg, function (hotpoints) {
                    self.fixContentHeight();
                    self.hotpoints = hotpoints.detections;
                    self.drawHotpoints(self.hotpoints);
                    var selectedItem = 0;
                    for (var i in self.hotpoints) {
                        if (self.hotpoints[i].xcenter == visideaitem_center[0] && self.hotpoints[i].ycenter == visideaitem_center[1])
                        selectedItem = i;
                    }
                    if (self.hotpoints.length)
                        self.selectHotpoint(self.hotpoints[selectedItem]);
                }
            );
        }
    }

    drawHotpoints(hotpoints)
    {
        var $img = jQuery('#visidea-visualsearch__upload-image');
        var self = this;

        var image = document.getElementById("visidea-visualsearch__upload-image");
        var dim = (image.width > image.height) ? image.width : image.height;
        // console.log(image.width);
        // console.log(image.height);
        // console.log(dim);
        // console.log(image.naturalWidth)
        // console.log(image.naturalHeight)

        var canvas = document.getElementById("visidea-visualsearch__upload-canvas");
        canvas.width = dim;
        canvas.height = dim;
        // console.log(canvas.width);
        // console.log(canvas.height);
        var resizeFactor = image.width/image.naturalWidth;
        this.resizeFactor = resizeFactor;

        // canvas.width = dim+'px';
        // canvas.height = dim+'px';
        var canvas = document.getElementById("visidea-visualsearch__upload-canvas");
        var ctx = canvas.getContext("2d");
        // ctx.fillStyle = "#FFFFFF";
        // ctx.fillRect(0, 0, 150, 75);
        hotpoints.forEach(
            function (element) {
                // console.log(element.xcenter)
                // console.log(element.ycenter)
                self.drawCircle(ctx, element.xcenter*resizeFactor, element.ycenter*resizeFactor, 5, 'white', 'black', 2);
            }
        );

    }

    drawCircle(ctx, x, y, radius, fill, stroke, strokeWidth)
    {
        const circle = new Path2D();
        circle.arc(x, y, radius, 0, 2 * Math.PI);
        ctx.fillStyle = fill;
        ctx.fill(circle);
        ctx.stroke_style = stroke;
        ctx.lineWidth = strokeWidth;
        ctx.stroke(circle);
    }

    selectHotpoint(elem)
    {
        // console.log(elem)

        var image = new Image();
        image.src = jQuery('.visidea-visualsearch__upload-image').attr('src');

        var self = this;

        image.onload = function () {
            // console.log(image.width)
            // console.log(image.height)
            // console.log(elem)
            // console.log(elem.xcenter-elem.width/2)
            // console.log(elem.ycenter-elem.height/2)
            // console.log(elem.width)
            // console.log(elem.height)
            const canvas = document.getElementById('visidea-visualsearch__upload-canvas-crop');
            canvas.width = elem.width;
            canvas.height = elem.height;
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(image, (elem.xcenter-elem.width/2), (elem.ycenter-elem.height/2), elem.width, elem.height, 0,0, elem.width, elem.height);
            // let imageData = ctx.getImageData(0, 0, image.width*elem.w, image.height*elem.h);
            // console.log(imageData)
            var jpg = canvas.toDataURL("image/jpeg");
            // console.log(jpg)
            // ctx.clearRect(0, 0, canvas.width, canvas.height);

            self.visualrecommend(jpg, elem.class);
        }

        const url = new URL(window.location);
        url.searchParams.set('visidea', 'visualsearch');
        url.searchParams.set('visideaitem', elem.xcenter+','+elem.ycenter);
        window.history.pushState({}, '', url);

    }

    visualrecommend(jpg, detected_class)
    {
        var self = this;
        var displayVRecommendations = function (res) {
            jQuery('.visidea-visualsearch__content').scrollTop(0);
            jQuery('.visidea-visualsearch__content-hook').html('');

            if (res[0] !== undefined) {
                var recomms_rows = res.map(
                    vals => self.renderProductVisual(vals['name'], vals['brand_name'], vals['url'], vals['images'][0], vals['price'])
                );
                jQuery('.visidea-visualsearch__content-hook').append(recomms_rows.join(''));
            }
        }
        this.visidea.visualrecommend(jpg, 100, detected_class, displayVRecommendations);
    }

    renderProductVisual(title, brand, link, image, price)
    {
        if (!title)
            title = "";
        if (!brand)
            brand = "";
        if (!link)
            link = "";
        if (!image)
            image = "";
        if (!price)
            price = "";

        return  [
            '    <div class="visidea__product">',
            '        <a href="' + link +'"><img src="' + image +'" class="visidea__product-image" alt="" /></a>',
            '        <div class="visidea__product-caption">',
            '            <p class="visidea__product-heading"><a href="' + link +'">' + title + '</a></p>',
            '            <p class="visidea__product-brand"><a href="' + link +'">' + brand + '</a></p>',
            '            <p class="visidea__product-price"><strong>' + this.visidea.format_currency(price) + '</strong></p>',
            // '            <a href="' + link +'" class="visidea__product-link btn btn-primary" role="button">See Details</a></p>',
            '        </div>',
            '    </div>',
            ].join('');
    }

    loadStyle(src)
    {
        var link = document.createElement('link');
        link.href = src;
        link.type = "text/css";
        link.rel = "stylesheet";
        link.media = "screen,print";
        document.head.appendChild(link);
    }

    loadCss(styles)
    {
        var styleSheet = document.createElement("style")
        styleSheet.type = "text/css"
        styleSheet.textContent = styles
        document.head.appendChild(styleSheet)
    }

    loadScript(src)
    {
        var script = document.createElement('script');
        script.onload = function () {
            //do stuff with the script
        };
        script.src = src;
        document.head.appendChild(script);
    }

    whenAvailable(name, callback)
    {
        var interval = 10; // ms
        var self = this;
        window.setTimeout(
            function () {
                if (window[name]) {
                    callback(window[name]);
                } else {
                    window.setTimeout(self.whenAvailable(name, callback), interval);
                }
            }, interval
        );
    }

    whenExists(name, callback)
    {
        var interval = 10; // ms
        window.setTimeout(
            function () {
                if (eval(name) != undefined) {
                    callback();
                } else {
                    window.setTimeout(arguments.callee, interval);
                }
            }, interval
        );
    }

}
