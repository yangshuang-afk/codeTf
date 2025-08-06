/*水印配置*/
function watermark(firstNode, secondNode) {
    //绑定容器
    var container = document.getElementsByClassName('elementdiv')
    if (container.length > 0) {
        for (var i = container.length - 1; i >= 0; i--) {
            document.body.removeChild(container[i])
        }
    }
  
    var elementWidth = document.documentElement.clientWidth+240; //返回元素的宽度（包括元素宽度、内边距和边框，不包括外边距）
    var elementHeight = document.documentElement.clientHeight+240; //返回元素的高度（包括元素高度、内边距和边框，不包括外边距）
  
    var intervalWidth = 140; //设置水印的间隔宽度
    var intervalheight = 140; //设置水印的间隔高度
  
    var crosswise = (elementWidth - 60 - 200) / intervalWidth; //水印的横向个数
    var lengthways = (elementHeight - 60 - 80) / intervalheight; //水印的纵向个数
  
    //水印默认配置
    var watermarkConfiguration = {
        watermarkFont: '宋体', //水印字体
        watermarkColor: '#999', //水印字体颜色
        watermarkFontsize: '15px', //水印字体大小
        watermarkTransparency: 0.15, //水印透明度
        watermarkWidth: 80, //水印宽度
        watermarkHeight: 40, //水印长度
        watermark_angle: 30 //水印倾斜度数
    };
    // 创建文档碎片节点(关键代码)
    var _template = document.createDocumentFragment();
    //遍历外层横向个数
    for (var i = 0; i < crosswise; i++) {
        //遍历内层纵向个数
        for (var j = 0; j < lengthways; j++) {
  
            var xaxis = intervalWidth * i + 26;
            if ((i+1) % 2 == 0){
              var yaxis = intervalheight * j + Math.floor(Math.random() * (50 - 26 + 1)) + 26;
            }else{
              var yaxis = intervalheight * j + 26;
            }
      
  
            //创建水印 
            var watermarkDiv = document.createElement('div');
  
            watermarkDiv.id = 'watermarkDiv' + i + j;
            watermarkDiv.className = 'watermarkDiv';
  
            ///节点创建
            var spanFirst = document.createElement('div'); //第一个节点
            var spanSecond = document.createElement('div'); //第二个节点
            spanFirst.appendChild(document.createTextNode(firstNode));
            spanSecond.appendChild(document.createTextNode(secondNode));
            watermarkDiv.appendChild(spanFirst);
            watermarkDiv.appendChild(spanSecond);
  
            /*样式配置*/
            //设置水印div倾斜显示
            watermarkDiv.style.webkitTransform = "rotate(-" + watermarkConfiguration.watermark_angle + "deg)"; //针对 safari 浏览器支持
            watermarkDiv.style.MozTransform = "rotate(-" + watermarkConfiguration.watermark_angle + "deg)";
            watermarkDiv.style.msTransform = "rotate(-" + watermarkConfiguration.watermark_angle + "deg)";
            watermarkDiv.style.OTransform = "rotate(-" + watermarkConfiguration.watermark_angle + "deg)";
            watermarkDiv.style.transform = "rotate(-" + watermarkConfiguration.watermark_angle + "deg)";
            watermarkDiv.style.opacity = watermarkConfiguration.watermarkTransparency; //水印透明度
            watermarkDiv.style.fontSize = watermarkConfiguration.watermarkFontsize; //水印字体大小
            watermarkDiv.style.fontFamily = watermarkConfiguration.watermarkFont; //水印字体
            watermarkDiv.style.color = watermarkConfiguration.watermarkColor; //水印颜色
            watermarkDiv.style.width = watermarkConfiguration.watermarkWidth + 'px'; //水印宽度
            watermarkDiv.style.height = watermarkConfiguration.watermarkHeight + 'px'; //水印高度
            watermarkDiv.style.visibility = ""; //看不见但是摸得着。
            watermarkDiv.style.position = "absolute"; //水印绝对定位
            watermarkDiv.style.left = xaxis + 'px'; //Y轴
            watermarkDiv.style.top = yaxis + 'px'; //X轴
            watermarkDiv.style.overflow = "hidden";
            watermarkDiv.style.zIndex = "9999"; //设置优先级
            watermarkDiv.style.pointerEvents = 'none'; //pointer-events:none  让水印不阻止交互事件
            watermarkDiv.style.textAlign = "center"; //水平居中
            watermarkDiv.style.display = "block"; //显示元素
  
            _template.appendChild(watermarkDiv); //从一个元素向另一个元素中移动元素
        }
    }
    document.body.appendChild(_template);
  }
  