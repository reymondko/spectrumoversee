
<!-- saved from url=(0044)http://www.digiance.com/zebra/demo/test.html -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <!-- Required scripts -->
  <script type="text/javascript" src="/js/zpl/zpl_files/rsvp-3.1.0.min.js.download"></script>
  <script type="text/javascript" src="/js/zpl/zpl_files/sha-256.min.js.download"></script>
  <script type="text/javascript" src="/js/zpl/zpl_files/qz-tray.js.download"></script>
  <script type="text/javascript" src="/js/zpl/zpl_files/jquery-1.11.3.min.js.download"></script>

</head>
<body>
  <!-- <a href="javascript:findDefaultPrinter();">Print Label</a> -->

<script>
  /// Authentication setup ///
  qz.security.setCertificatePromise(function(resolve, reject) {
      //Preferred method - from server
//        $.ajax({ url: "assets/signing/digital-certificate.txt", cache: false, dataType: "text" }).then(resolve, reject);

      //Alternate method 1 - anonymous
//        resolve();

      //Alternate method 2 - direct
      resolve("-----BEGIN CERTIFICATE-----\n" +
              "MIIFAzCCAuugAwIBAgICEAIwDQYJKoZIhvcNAQEFBQAwgZgxCzAJBgNVBAYTAlVT\n" +
              "MQswCQYDVQQIDAJOWTEbMBkGA1UECgwSUVogSW5kdXN0cmllcywgTExDMRswGQYD\n" +
              "VQQLDBJRWiBJbmR1c3RyaWVzLCBMTEMxGTAXBgNVBAMMEHF6aW5kdXN0cmllcy5j\n" +
              "b20xJzAlBgkqhkiG9w0BCQEWGHN1cHBvcnRAcXppbmR1c3RyaWVzLmNvbTAeFw0x\n" +
              "NTAzMTkwMjM4NDVaFw0yNTAzMTkwMjM4NDVaMHMxCzAJBgNVBAYTAkFBMRMwEQYD\n" +
              "VQQIDApTb21lIFN0YXRlMQ0wCwYDVQQKDAREZW1vMQ0wCwYDVQQLDAREZW1vMRIw\n" +
              "EAYDVQQDDAlsb2NhbGhvc3QxHTAbBgkqhkiG9w0BCQEWDnJvb3RAbG9jYWxob3N0\n" +
              "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtFzbBDRTDHHmlSVQLqjY\n" +
              "aoGax7ql3XgRGdhZlNEJPZDs5482ty34J4sI2ZK2yC8YkZ/x+WCSveUgDQIVJ8oK\n" +
              "D4jtAPxqHnfSr9RAbvB1GQoiYLxhfxEp/+zfB9dBKDTRZR2nJm/mMsavY2DnSzLp\n" +
              "t7PJOjt3BdtISRtGMRsWmRHRfy882msBxsYug22odnT1OdaJQ54bWJT5iJnceBV2\n" +
              "1oOqWSg5hU1MupZRxxHbzI61EpTLlxXJQ7YNSwwiDzjaxGrufxc4eZnzGQ1A8h1u\n" +
              "jTaG84S1MWvG7BfcPLW+sya+PkrQWMOCIgXrQnAsUgqQrgxQ8Ocq3G4X9UvBy5VR\n" +
              "CwIDAQABo3sweTAJBgNVHRMEAjAAMCwGCWCGSAGG+EIBDQQfFh1PcGVuU1NMIEdl\n" +
              "bmVyYXRlZCBDZXJ0aWZpY2F0ZTAdBgNVHQ4EFgQUpG420UhvfwAFMr+8vf3pJunQ\n" +
              "gH4wHwYDVR0jBBgwFoAUkKZQt4TUuepf8gWEE3hF6Kl1VFwwDQYJKoZIhvcNAQEF\n" +
              "BQADggIBAFXr6G1g7yYVHg6uGfh1nK2jhpKBAOA+OtZQLNHYlBgoAuRRNWdE9/v4\n" +
              "J/3Jeid2DAyihm2j92qsQJXkyxBgdTLG+ncILlRElXvG7IrOh3tq/TttdzLcMjaR\n" +
              "8w/AkVDLNL0z35shNXih2F9JlbNRGqbVhC7qZl+V1BITfx6mGc4ayke7C9Hm57X0\n" +
              "ak/NerAC/QXNs/bF17b+zsUt2ja5NVS8dDSC4JAkM1dD64Y26leYbPybB+FgOxFu\n" +
              "wou9gFxzwbdGLCGboi0lNLjEysHJBi90KjPUETbzMmoilHNJXw7egIo8yS5eq8RH\n" +
              "i2lS0GsQjYFMvplNVMATDXUPm9MKpCbZ7IlJ5eekhWqvErddcHbzCuUBkDZ7wX/j\n" +
              "unk/3DyXdTsSGuZk3/fLEsc4/YTujpAjVXiA1LCooQJ7SmNOpUa66TPz9O7Ufkng\n" +
              "+CoTSACmnlHdP7U9WLr5TYnmL9eoHwtb0hwENe1oFC5zClJoSX/7DRexSJfB7YBf\n" +
              "vn6JA2xy4C6PqximyCPisErNp85GUcZfo33Np1aywFv9H+a83rSUcV6kpE/jAZio\n" +
              "5qLpgIOisArj1HTM6goDWzKhLiR/AeG3IJvgbpr9Gr7uZmfFyQzUjvkJ9cybZRd+\n" +
              "G8azmpBBotmKsbtbAU/I/LVk8saeXznshOVVpDRYtVnjZeAneso7\n" +
              "-----END CERTIFICATE-----\n" +
              "--START INTERMEDIATE CERT--\n" +
              "-----BEGIN CERTIFICATE-----\n" +
              "MIIFEjCCA/qgAwIBAgICEAAwDQYJKoZIhvcNAQELBQAwgawxCzAJBgNVBAYTAlVT\n" +
              "MQswCQYDVQQIDAJOWTESMBAGA1UEBwwJQ2FuYXN0b3RhMRswGQYDVQQKDBJRWiBJ\n" +
              "bmR1c3RyaWVzLCBMTEMxGzAZBgNVBAsMElFaIEluZHVzdHJpZXMsIExMQzEZMBcG\n" +
              "A1UEAwwQcXppbmR1c3RyaWVzLmNvbTEnMCUGCSqGSIb3DQEJARYYc3VwcG9ydEBx\n" +
              "emluZHVzdHJpZXMuY29tMB4XDTE1MDMwMjAwNTAxOFoXDTM1MDMwMjAwNTAxOFow\n" +
              "gZgxCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOWTEbMBkGA1UECgwSUVogSW5kdXN0\n" +
              "cmllcywgTExDMRswGQYDVQQLDBJRWiBJbmR1c3RyaWVzLCBMTEMxGTAXBgNVBAMM\n" +
              "EHF6aW5kdXN0cmllcy5jb20xJzAlBgkqhkiG9w0BCQEWGHN1cHBvcnRAcXppbmR1\n" +
              "c3RyaWVzLmNvbTCCAiIwDQYJKoZIhvcNAQEBBQADggIPADCCAgoCggIBANTDgNLU\n" +
              "iohl/rQoZ2bTMHVEk1mA020LYhgfWjO0+GsLlbg5SvWVFWkv4ZgffuVRXLHrwz1H\n" +
              "YpMyo+Zh8ksJF9ssJWCwQGO5ciM6dmoryyB0VZHGY1blewdMuxieXP7Kr6XD3GRM\n" +
              "GAhEwTxjUzI3ksuRunX4IcnRXKYkg5pjs4nLEhXtIZWDLiXPUsyUAEq1U1qdL1AH\n" +
              "EtdK/L3zLATnhPB6ZiM+HzNG4aAPynSA38fpeeZ4R0tINMpFThwNgGUsxYKsP9kh\n" +
              "0gxGl8YHL6ZzC7BC8FXIB/0Wteng0+XLAVto56Pyxt7BdxtNVuVNNXgkCi9tMqVX\n" +
              "xOk3oIvODDt0UoQUZ/umUuoMuOLekYUpZVk4utCqXXlB4mVfS5/zWB6nVxFX8Io1\n" +
              "9FOiDLTwZVtBmzmeikzb6o1QLp9F2TAvlf8+DIGDOo0DpPQUtOUyLPCh5hBaDGFE\n" +
              "ZhE56qPCBiQIc4T2klWX/80C5NZnd/tJNxjyUyk7bjdDzhzT10CGRAsqxAnsjvMD\n" +
              "2KcMf3oXN4PNgyfpbfq2ipxJ1u777Gpbzyf0xoKwH9FYigmqfRH2N2pEdiYawKrX\n" +
              "6pyXzGM4cvQ5X1Yxf2x/+xdTLdVaLnZgwrdqwFYmDejGAldXlYDl3jbBHVM1v+uY\n" +
              "5ItGTjk+3vLrxmvGy5XFVG+8fF/xaVfo5TW5AgMBAAGjUDBOMB0GA1UdDgQWBBSQ\n" +
              "plC3hNS56l/yBYQTeEXoqXVUXDAfBgNVHSMEGDAWgBQDRcZNwPqOqQvagw9BpW0S\n" +
              "BkOpXjAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBAQAJIO8SiNr9jpLQ\n" +
              "eUsFUmbueoxyI5L+P5eV92ceVOJ2tAlBA13vzF1NWlpSlrMmQcVUE/K4D01qtr0k\n" +
              "gDs6LUHvj2XXLpyEogitbBgipkQpwCTJVfC9bWYBwEotC7Y8mVjjEV7uXAT71GKT\n" +
              "x8XlB9maf+BTZGgyoulA5pTYJ++7s/xX9gzSWCa+eXGcjguBtYYXaAjjAqFGRAvu\n" +
              "pz1yrDWcA6H94HeErJKUXBakS0Jm/V33JDuVXY+aZ8EQi2kV82aZbNdXll/R6iGw\n" +
              "2ur4rDErnHsiphBgZB71C5FD4cdfSONTsYxmPmyUb5T+KLUouxZ9B0Wh28ucc1Lp\n" +
              "rbO7BnjW\n" +
              "-----END CERTIFICATE-----\n");
  });

  qz.security.setSignaturePromise(function(toSign) {
      return function(resolve, reject) {
          //Preferred method - from server
//            $.ajax("/secure/url/for/sign-message?request=" + toSign).then(resolve, reject);

          //Alternate method - unsigned
          resolve();
      };
  });

  function setPrinter(printer) {
      var cf = getUpdatedConfig();
      cf.setPrinter(printer);

      if (printer && typeof printer === 'object' && printer.name == undefined) {
          var shown;
          if (printer.file != undefined) {
              shown = "<em>FILE:</em> " + printer.file;
          }
          if (printer.host != undefined) {
              shown = "<em>HOST:</em> " + printer.host + ":" + printer.port;
          }

          $("#configPrinter").html(shown);
      } else {
          if (printer && printer.name != undefined) {
              printer = printer.name;
          }

          if (printer == undefined) {
              printer = 'NONE';
          }
          $("#configPrinter").html(printer);
      }
  }

  function findDefaultPrinter() {


    /** Localhost Printing **/
    setPrinter({ host: '127.0.0.1', port: '9100'});
    var config = getUpdatedConfig();
    var printData = [
          '^XA\n',
          '^CF0,60\n',
          '^FO50,50^GB100,100,100^FS\n',
          '^FO75,75^FR^GB100,100,100^FS\n',
          '^FO88,88^GB50,50,50^FS\n',
          '^FO220,50^FDInternational Shipping, Inc.^FS\n',
          '^CF0,40\n',
          '^FO220,100^FD1000 Shipping Lane^FS\n',
          '^FO220,135^FDShelbyville TN 38102^FS\n',
          '^FO220,170^FDUnited States (USA)^FS\n',
          '^FO50,250^GB700,1,3^FS\n',
          '^CFA,30\n',
          '^FO50,300^FDJohn Doe^FS\n',
          '^FO50,340^FD100 Main Street^FS\n',
          '^FO50,380^FDSpringfield TN 39021^FS\n',
          '^FO50,420^FDUnited States (USA)^FS\n',
          '^CFA,15\n',
          '^FO600,300^GB150,150,3^FS\n',
          '^FO638,340^FDPermit^FS\n',
          '^FO638,390^FD123456^FS\n',
          '^FO50,500^GB700,1,3^FS\n',
          '^BY5,2,270\n',
          '^FO100,550^BC^FD12345678^FS\n',
          '^FO50,900^GB700,250,3^FS\n',
          '^FO400,900^GB1,250,3^FS\n',
          '^CF0,40\n',
          '^FO100,960^FDShipping Ctr. X34B-1^FS\n',
          '^FO100,1010^FDREF1 F00B47^FS\n',
          '^FO100,1060^FDREF2 BL4H8^FS\n',
          '^CF0,190\n',
          '^FO485,965^FDCA^FS\n',
          '^XZ\n'
        ];

    qz.print(config, printData).catch(displayError);

    /**
    console.log('printing now');
      qz.printers.getDefault().then(function(printername) {
        console.log('print to ' + printername);
        var config = qz.configs.create(printername);       // Create a default config for the found printer

        var printData = [
          '^XA\n',
          '^CF0,60\n',
          '^FO50,50^GB100,100,100^FS\n',
          '^FO75,75^FR^GB100,100,100^FS\n',
          '^FO88,88^GB50,50,50^FS\n',
          '^FO220,50^FDInternational Shipping, Inc.^FS\n',
          '^CF0,40\n',
          '^FO220,100^FD1000 Shipping Lane^FS\n',
          '^FO220,135^FDShelbyville TN 38102^FS\n',
          '^FO220,170^FDUnited States (USA)^FS\n',
          '^FO50,250^GB700,1,3^FS\n',
          '^CFA,30\n',
          '^FO50,300^FDJohn Doe^FS\n',
          '^FO50,340^FD100 Main Street^FS\n',
          '^FO50,380^FDSpringfield TN 39021^FS\n',
          '^FO50,420^FDUnited States (USA)^FS\n',
          '^CFA,15\n',
          '^FO600,300^GB150,150,3^FS\n',
          '^FO638,340^FDPermit^FS\n',
          '^FO638,390^FD123456^FS\n',
          '^FO50,500^GB700,1,3^FS\n',
          '^BY5,2,270\n',
          '^FO100,550^BC^FD12345678^FS\n',
          '^FO50,900^GB700,250,3^FS\n',
          '^FO400,900^GB1,250,3^FS\n',
          '^CF0,40\n',
          '^FO100,960^FDShipping Ctr. X34B-1^FS\n',
          '^FO100,1010^FDREF1 F00B47^FS\n',
          '^FO100,1060^FDREF2 BL4H8^FS\n',
          '^CF0,190\n',
          '^FO485,965^FDCA^FS\n',
          '^XZ\n'
        ];

        qz.print(config, printData).catch(displayError);

      }).catch(displayError);
      **/
  }

  /// Connection ///
  function launchQZ() {
      if (!qz.websocket.isActive()) {
          window.location.assign("qz:launch");
          //Retry 5 times, pausing 1 second between each attempt
          startConnection({ retries: 5, delay: 1 });
      }
  }

  function startConnection(config) {
      if (!qz.websocket.isActive()) {
          updateState('Waiting', 'default');

          qz.websocket.connect(config).then(function() {
              updateState('Active', 'success');
              findVersion();
            //   findDefaultPrinter();
            printBase64();
          }).catch(handleConnectionError);
      } else {
          displayMessage('An active connection with QZ already exists.', 'alert-warning');
      }
  }

  function printZPL() {
      var config = getUpdatedConfig();

      var printData = [
          '^XA\n',
          '^FO50,100',
          { type: 'raw', format: 'image', data: 'assets/img/image_sample_bw.png', options: { language: 'ZPLII' } },
    '^FS\n',
    '^FO50,300^ADN,36,20^FDPRINTED USING^FS\n',
          '^FO50,350^ADN,36,20^FDQZ-TRAY ^FS\n',
    '^FO50,400^ADN,36,20^FDVERSION ' + qzVersion + '^FS\n' + '\n',
    '^XZ\n'
      ];

      qz.print(config, printData).catch(displayError);
  }

  var qzVersion = 0;
  function findVersion() {
      qz.api.getVersion().then(function(data) {
          $("#qz-version").html(data);
          qzVersion = data;
      }).catch(displayError);
  }

  $("#askFileModal").on("shown.bs.modal", function() {
      $("#askFile").focus().select();
  });
  $("#askHostModal").on("shown.bs.modal", function() {
      $("#askHost").focus().select();
  });


  /// Helpers ///
  function handleConnectionError(err) {
      updateState('Error', 'danger');

      if (err.target != undefined) {
          if (err.target.readyState >= 2) { //if CLOSING or CLOSED
              displayError("Connection to QZ Tray was closed");
          } else {
              displayError("A connection error occurred, check log for details");
              console.error(err);
          }
      } else {
          displayError(err);
      }
  }

  function displayError(err) {
      console.error(err);
      displayMessage(err, 'alert-danger');
  }

  function endConnection() {
      if (qz.websocket.isActive()) {
          qz.websocket.disconnect().then(function() {
              updateState('Inactive', 'default');
          }).catch(handleConnectionError);
      } else {
          displayMessage('No active connection with QZ exists.', 'alert-warning');
      }
  }

  function updateState(text, css) {
      $("#qz-status").html(text);
      $("#qz-connection").removeClass().addClass('panel panel-' + css);

      if (text === "Inactive" || text === "Error") {
          $("#launch").show();
      } else {
          $("#launch").hide();
      }
  }

  function displayMessage(msg, css) {
      /*if (css == undefined) { css = 'alert-info'; }

      var timeout = setTimeout(function() { $('#' + timeout).alert('close'); }, 5000);

      var alert = $("<div/>").addClass('alert alert-dismissible fade in ' + css)
              .css('max-height', '20em').css('overflow', 'auto')
              .attr('id', timeout).attr('role', 'alert');
      alert.html("<button type='button' class='close' data-dismiss='alert'>&times;</button>" + msg);

      $("#qz-alert").append(alert);*/
      alert(msg)
  }

  /// Resets ///
  function resetRawOptions() {
      $("#rawPerSpool").val(1);
      $("#rawEncoding").val(null);
      $("#rawEndOfDoc").val(null);
      $("#rawAltPrinting").prop('checked', false);
      $("#rawCopies").val(1);
  }

  function printBase64() {

      // Send base64 encoded characters/raw commands to qz using data type 'base64'.
      // This will automatically convert provided base64 encoded text into text/ascii/bytes, etc.
      // This example is for EPL and contains an embedded image.
      // Please adapt to your printer language.

      //noinspection SpellCheckingInspection
      var printData = [
          {
              type: 'raw',
              format: 'base64',
              data: ''
          }
      ];

    //   var printData = [
    //       '^XA\n',
    //       '^CF0,60\n',
    //       '^FO50,50^GB100,100,100^FS\n',
    //       '^FO75,75^FR^GB100,100,100^FS\n',
    //       '^FO88,88^GB50,50,50^FS\n',
    //       '^FO220,50^FDInternational Shipping, Inc.^FS\n',
    //       '^CF0,40\n',
    //       '^FO220,100^FD1000 Shipping Lane^FS\n',
    //       '^FO220,135^FDShelbyville TN 38102^FS\n',
    //       '^FO220,170^FDUnited States (USA)^FS\n',
    //       '^FO50,250^GB700,1,3^FS\n',
    //       '^CFA,30\n',
    //       '^FO50,300^FDJohn Doe^FS\n',
    //       '^FO50,340^FD100 Main Street^FS\n',
    //       '^FO50,380^FDSpringfield TN 39021^FS\n',
    //       '^FO50,420^FDUnited States (USA)^FS\n',
    //       '^CFA,15\n',
    //       '^FO600,300^GB150,150,3^FS\n',
    //       '^FO638,340^FDPermit^FS\n',
    //       '^FO638,390^FD123456^FS\n',
    //       '^FO50,500^GB700,1,3^FS\n',
    //       '^BY5,2,270\n',
    //       '^FO100,550^BC^FD12345678^FS\n',
    //       '^FO50,900^GB700,250,3^FS\n',
    //       '^FO400,900^GB1,250,3^FS\n',
    //       '^CF0,40\n',
    //       '^FO100,960^FDShipping Ctr. X34B-1^FS\n',
    //       '^FO100,1010^FDREF1 F00B47^FS\n',
    //       '^FO100,1060^FDREF2 BL4H8^FS\n',
    //       '^CF0,190\n',
    //       '^FO485,965^FDCA^FS\n',
    //       '^XZ\n'
    //     ];


    /** LOCAL EMULATION **/
    // setPrinter({ host: '127.0.0.1', port: '9100'});
    // var config = getUpdatedConfig();
    // qz.print(config, printData).catch(displayError);

    /** DEFULT PRINTER **/
    // qz.printers.getDefault().then(function(printername) {
    //     var config = qz.configs.create(printername);
    //     qz.print(config, printData).catch(displayError);
    // }).catch(displayError);

    // var zpl = getZplArrayFromURL('zpl')
    var zpl = '{{ implode(',', json_decode($shipment->base_64_zpl)) }}';
    zpl = zpl.split(',');
    console.log(zpl);

     if(zpl != null || zpl != undefined){

        //  console.log(zpl);
        zpl.forEach(function(z){
            var printData = [{
                type: 'raw',
                format: 'base64',
                data:z
            }];

            /** LOCAL EMULATION
            setPrinter({ host: '127.0.0.1', port: '9100'});
            var config = getUpdatedConfig();
            qz.print(config, printData).catch(displayError);
            **/
            /** DEFULT PRINTER**/
            qz.printers.getDefault().then(function(printername) {
                var config = qz.configs.create(printername);
                qz.print(config, printData).catch(displayError);
            }).catch(displayError);


        });



     }

  }

  /// QZ Config ///
  var cfg = null;
  function getUpdatedConfig() {
      if (cfg == null) {
          cfg = qz.configs.create(null);
      }

      updateConfig();
      return cfg
  }

  function updateConfig() {
      var pxlSize = null;
      if ($("#pxlSizeActive").prop('checked')) {
          pxlSize = {
              width: $("#pxlSizeWidth").val(),
              height: $("#pxlSizeHeight").val()
          };
      }

      var pxlMargins = $("#pxlMargins").val();
      if ($("#pxlMarginsActive").prop('checked')) {
          pxlMargins = {
              top: $("#pxlMarginsTop").val(),
              right: $("#pxlMarginsRight").val(),
              bottom: $("#pxlMarginsBottom").val(),
              left: $("#pxlMarginsLeft").val()
          };
      }

      var copies = 1;
      var jobName = null;
      if ($("#rawTab").hasClass("active")) {
          copies = $("#rawCopies").val();
          jobName = $("#rawJobName").val();
      } else {
          copies = $("#pxlCopies").val();
          jobName = $("#pxlJobName").val();
      }

      cfg.reconfigure({
                          altPrinting: $("#rawAltPrinting").prop('checked'),
                          encoding: $("#rawEncoding").val(),
                          endOfDoc: $("#rawEndOfDoc").val(),
                          perSpool: $("#rawPerSpool").val(),

                          colorType: $("#pxlColorType").val(),
                          copies: copies,
                          density: $("#pxlDensity").val(),
              duplex: $("#pxlDuplex").prop('checked'),
                          interpolation: $("#pxlInterpolation").val(),
                          jobName: jobName,
                          legacy: $("#pxlLegacy").prop('checked'),
                          margins: pxlMargins,
                          orientation: $("#pxlOrientation").val(),
                          paperThickness: $("#pxlPaperThickness").val(),
                          printerTray: $("#pxlPrinterTray").val(),
                          rasterize: $("#pxlRasterize").prop('checked'),
                          rotation: $("#pxlRotation").val(),
                          scaleContent: $("#pxlScale").prop('checked'),
                          size: pxlSize,
                          units: $("input[name='pxlUnits']:checked").val()
                      });
  }

    function getZplArrayFromURL(parameterName) {
        var result = [],
            tmp = [];
        location.search
            .substr(1)
            .split("&")
            .forEach(function (item) {
                tmp = item.split("=");
                if (tmp[0] === parameterName)
                result = tmp;
            });
        result.shift();
        return result;
    }

  /// Page load ///
  $(document).ready(function() {
      window.readingWeight = false;

      resetRawOptions();
      startConnection();



  });
</script>

</body></html>
