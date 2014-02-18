<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Write to your MP about care.data</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
  </head>
  <body>

    <div class="container">

      <div class="row">
        <div class="col-sm-6 col-sm-offset-3">

          <div class="form-group">
            <label for="postcode">Please Enter Your Postcode:</label>
            <div class="input-group">
              <input type="text" class="form-control" id="postcode">
              <span class="input-group-btn">
                <button class="btn btn-default" type="button" id="find-mp">Find MP</button>
              </span>
            </div><!-- /input-group -->
          </div><!-- /form-group -->

        </div><!-- /col-sm-6 /col-sm-offset-3 -->
      </div><!-- /row -->

      <div class="row">
        <div class="col-sm-6 col-sm-offset-3">

          <div class="form-group">
            <label for="message">Your Message:</label>
            <textarea class="form-control" rows="10" id="message"></textarea>
          </div><!-- /form-group -->

          <div class="row">
            <div class="col-sm-6">

              <div class="form-group">
                <label for="name">Your Name:</label>
                <input type="text" class="form-control" id="name">
              </div><!-- /form-group -->

            </div><!-- /col-sm-6 -->
            <div class="col-sm-6">

              <div class="form-group">
                <label for="email">Your Email:</label>
                <input type="email" class="form-control" id="email">
              </div><!-- /form-group -->

            </div><!-- /col-sm-6 -->
          </div><!-- /row -->

          <div class="row">
            <div class="col-sm-6">

              <div class="form-group">
                <label for="address">Your Address:</label>
                <input type="text" class="form-control" id="address">
              </div><!-- /form-group -->

            </div><!-- /col-sm-6 -->
            <div class="col-sm-6">

              <div class="form-group">
                <label for="town">Your Town/City:</label>
                <input type="text" class="form-control" id="town">
              </div><!-- /form-group -->

            </div><!-- /col-sm-6 -->
          </div><!-- /row -->

        </div><!-- /col-sm-6 /col-sm-offset-3 -->
      </div><!-- /row -->

      <div class="row">
        <div class="col-sm-6 col-sm-offset-3" id="send-container">

          <button class="btn btn-default" type="button" id="send">Send Message</button>

        </div><!-- /col-sm-6 /col-sm-offset-3 -->
      </div><!-- /row -->

    </div><!-- /container -->

    <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <script>
      $(function(){
        mpData = {};

        $('#find-mp').click(function(){
          $.get('/api/email/' + $('#postcode').val() +'?_token={{ Session::token() }}', function(data){
            mpData = data;
            $('#message').text('Dear '+data.addressAs+',\n\n\n\nYours sincerely,');
          });
        });

        $('#send').click(function(){
          var message = encodeURIComponent($('#name').val()+',\n'
                                          +$('#address').val()+',\n'
                                          +$('#town').val()+'\n'
                                          +$('#postcode').val()+'\n'
                                          +$('#email').val()+'\n\n'
                                          +$('#message').text()+'\n'
                                          +$('#name').val());
          $('#send-container').html('<a class="btn btn-default" role="button" href="mailto:'+mpData.contact.Parliamentary.email+'?subject=care.data&body='+message+'">Are You Sure?</a>')
        });
      });
    </script>
  </body>
</html>
