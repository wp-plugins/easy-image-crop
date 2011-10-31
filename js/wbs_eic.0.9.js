var $wbsQuery = jQuery.noConflict();

$wbsQuery(document).ready(function($)
{

   $('img').each(function(i, item)
   {
      
      if($(this).attr('src').indexOf('uploads') !== -1)
      {
         $(this).addClass('img-'+i);
         $('body').data('thumb', $(this));
         var id = $(item).attr('id');
         var width = $(item).attr('width');
         var height = $(item).attr('height');
         var top, left, imgX, imgY, imgW, imgH = 0;
         var pos = $(item).offset();
         top = pos.top;
         left = pos.left;
         $('body').data('thumbT', top);
         $('body').data('thumbL', left);
         $('body').data('thumbW', width);
         $('body').data('thumbH', height);


         $('body').append('<div style="top:' + top + 'px; left: ' + left + 'px" id="edit" class="edit-' + i + '"></div>');
      
         $('.edit-' + i).bind('click', {i: i, thm_x: left, thm_y: top, thm_w: width, thm_h: height}, function(el)
         {
            var src = $('.img-'+ el.data.i).attr('src');
            var pos = 0;
            var tmpPos = src.indexOf('-');
            var ext = src.substr(src.length - 3, src.length);
            while(tmpPos > -1)
            {
               tmpPos = src.indexOf('-', tmpPos + 1);
               if(tmpPos > -1)
                  pos = tmpPos;
            }
            
               
            org_dst_file = $('.img-' + el.data.i).attr('src');
            dst_file = get_filename(org_dst_file) + '.' + ext;
            src = src.substr(0, pos) + '.' + ext;

             
            setup_boxes(el.data.src_id, src, el.data.thm_y, el.data.thm_x, el.data.thm_w, el.data.thm_h);
            
            var org_src_h = $('#edit_image').height();
            var org_src_w = $('#edit_image').width();
      
            $('#resize_box').draggable();
            $('#resize_box').resizable();
      
            $('#resize_box').bind( "drag", function(event, ui) {
                $('#edit_image_box').css('top', $('#resize_box').position().top);
                $('#edit_image_box').css('left', $('#resize_box').position().left);
            });
            
            $('#resize_box').bind( "resize", function(event, ui) {
                $('#edit_image_box img').css('width', $('#resize_box').width());
                $('#edit_image_box img').css('height', $('#resize_box').height());
            });
            
            $('#edit_cancel').bind('click', function(ev) {
                $('#edit_box').remove();
            });
            
            $('#edit_info').bind('click', function(ev) {
                alert('Drag the the image around and pull the bottom right corner to resize (hold shift to constrain proportions).\r\n\Click the crop button to crop the image');
            });
            
            
            $('#edit_crop').bind('click', {img_src: src, src_id: el.data.src_id, thm_w: width, thm_h: height, thm_x: el.data.thm_x, thm_y: el.data.thm_y, o_src_w: org_src_w, o_src_h: org_src_h}, function(ev)
            {
               var data =
               {
                  action: 'wbs_eic',
                  src_id: $('#' + get_filename(src)).text(),
                  dst_file: dst_file,
                  src_x: ev.data.thm_x - $('#edit_image_box').position().left,
                  src_y: ev.data.thm_y - $('#edit_image_box').position().top,
                  src_w: Math.round(org_src_w / Math.round($('#resize_box').width()) * ev.data.thm_w),
                  src_h: Math.round(org_src_h / Math.round($('#resize_box').height()) * ev.data.thm_h),
                  dst_w: ev.data.thm_w,
                  dst_h: ev.data.thm_h
               };
            
               var str_data
               jQuery.post(wbs_eic.ajaxurl, data, function(response) {
                  if(response != 0 && response != -1)
                  {
                     var today = new Date();
                     $(ev.data.src_id).attr('src', $(ev.data.src_id).attr('src')+'?'+today.getTime());
                     $('#edit_box').remove();
                  }
                  else
                  {
                     alert('something went wrong (' + response + ')');
                  }
               });
            });
         });
      }
   });
   
   function get_filename(url)
   {
      if (url)
      {
         var m = url.toString().match(/.*\/(.+?)\./);
         if (m && m.length > 1)
         {
            return m[1];
         }
      }
      return "";
   }

      
   function setup_boxes(id, src, thumb_top, thumb_left, thumb_width, thumb_height)
   {
      var boxX, boxY, boxW, boxH, aX, aY, bX, bY = 0;
      $('body').append('<div id="edit_box" style="width: 100%; height: 100%; position: aboslute; top: 0px; left: 0px"></div>');
      $('#edit_box').append('<div id="edit_image_box"><img id="edit_image" src="' + src + '"></div>');
      $('#edit_box').append('<div id="resize_box" class="resizeable draggable" style="border: ' + wbs_eic.border_width + 'px ' + wbs_eic.border_type + ' ' + wbs_eic.border_color + ';"></div>');
      
      $('#edit_box').data('edit_image_box', $('#edit_image_box'));

      boxX = $('#edit_image_box').position().left;
      boxY = $('#edit_image_box').position().top;
      boxW = $('#edit_image_box').width();
      boxH = $('#edit_image_box').height();
  
      imgX = thumb_left;
      imgY = thumb_top;
      imgW = thumb_width;
      imgH = thumb_height;
      
      aX = imgX + (imgW * 0.5);
      aY = imgY + (imgH * 0.5);
      bX = Math.round(aX - (boxW * 0.5));
      bY = Math.round(aY - (boxH * 0.5));

      $('#edit_image_box').css('left', bX);
      $('#edit_image_box').css('top', bY);
      $('#resize_box').css('left', bX);
      $('#resize_box').css('top', bY);
      $('#resize_box').css('width', boxW);
      $('#resize_box').css('height', boxH);
      $('body').data('edit_image_box', $('#edit_image_box'));
      
      $('#edit_box').append('<div style="background-color: #000000; z-index: 90; position: absolute; top: 0px; left: 0px; opacity: ' + wbs_eic.background_opacity + '; width:200%; height:' + imgY + 'px;"></div>');
      $('#edit_box').append('<div style="background-color: #000000; z-index: 90; position: absolute; top: ' + imgY + 'px; left: 0px; opacity: ' + wbs_eic.background_opacity + '; width:' + imgX + 'px; height:' + imgH + 'px;"></div>');
      $('#edit_box').append('<div style="background-color: #000000; z-index: 90; position: absolute; top: ' + imgY + 'px; left: ' + (parseInt(imgX) + parseInt(imgW)) + 'px; opacity: ' + wbs_eic.background_opacity + '; width: 200%; height:' + imgH + 'px;"></div>');
      $('#edit_box').append('<div style="background-color: #000000; z-index: 90; position: absolute; top: ' + (parseInt(imgY) + parseInt(imgH)) + 'px; left: 0px; opacity: ' + wbs_eic.background_opacity + '; width:200%; height:100%;"></div>');
      $('#edit_box').append('<div id="edit_buttons" style="z-index: 99; position: absolute; left: ' + (parseInt(imgX) - 45) + 'px; top: ' + imgY + 'px;"><div id="edit_info"></div><div id="edit_crop"></div><div id="edit_cancel"></div><div style="position: absolute; top: ' + (parseInt(imgY) + (parseInt(imgY) / 2) - 25) + 'px;left: ' + (parseInt(imgX) + (parseInt(imgW) / 2) - 25) +'px; z-index: 99;" id="loader"></div>');
   }
   
});
