$(document).ready(function(){
   /* $('#global_search').bind("enterKey",function(e){
    });
    $('input').keyup(function(e){
      if(e.keyCode == 13)
      {
        if($('#global_search').val() != ""){   
            window.location.href = '/search?s='+$('#global_search').val();
           }
      }
    });
    */
    $.get( "/notifications/count", function( data ) {
        if(data.status == "ok"){
          if(data.result.total > 0){
            $('.label-notifications_label').css('background-color','#f39c12');
            $('.label-notifications_label').html(data.result.total);
          }else{
            $('.label-notifications_label').css('background-color','#337ab7');
            $('.label-notifications_label').html(0);
          }
        }
    });

    
    $('#notificationModal').modal({ show: false})
    $( "body" ).append( `
    <div class="modal right fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel">
       <div class="modal-dialog" role="document">
          <div class="modal-content">
             <div class="modal-header notification-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="notificationModalLabel">NOTIFICATIONS</h4>
             </div>
             <div id="notification-modal-body" class="modal-body">
                <div class="notification-loader-container">
                  <center>
                  <div class="notification-loader"></div>
                  </center>
                </div>
                <div class="notification-content">
                </div>
             </div>
          </div>
          <!-- modal-content -->
       </div>
       <!-- modal-dialog -->
    </div>` );

    $('a[href="#showNotifications"]').click(function(){
      $('#notificationModal').modal('show');
    });

    $('#notificationModal').on('show.bs.modal', function() {
      $.get( "/notifications/all", function( data ) {
        if(data.status == "ok"){
          var notifications = data.result.notifications;
          $('.notification-loader-container').hide();
          $.each(notifications,function( key, value ) {
            if(value.type == 'alert'){
              var itemToAppend = `<div class="callout callout-warning">
                                <h4><i class="icon fa fa-warning"></i> ${value.message}</h4>
                                </div>`;
            }else if(value.type == 'new_order'){
               var itemToAppend = `<div class="callout callout-info">
                                    <h4>New Order</h4>
                                    <p>${value.message}</p>
                                </div>`;
            }
            $('.notification-content').append(itemToAppend);
          })
          
        }
      });
    });

    $('#notificationModal').on('hide.bs.modal', function() {
      $('.label-notifications_label').css('background-color','#337ab7');
      $('.label-notifications_label').html(0);
      $('.notification-content').html('');
      $('.notification-loader-container').show();
    });

    $('#topbar_search').click(function(){
      if($('#global_search').val() != ""){   
        window.location.href = '/search?s='+$('#global_search').val();
       }
    })
   
 });