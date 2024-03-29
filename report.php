<?php
include("php/dbconnect.php");
include("php/checklogin.php");

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sistema de Pago Escolar</title>

  <!-- BOOTSTRAP STYLES-->
  <link href="css/bootstrap.css" rel="stylesheet" />
  <!-- FONTAWESOME STYLES-->
  <link href="css/font-awesome.css" rel="stylesheet" />
  <!--CUSTOM BASIC STYLES-->
  <link href="css/basic.css" rel="stylesheet" />
  <!--CUSTOM MAIN STYLES-->
  <link href="css/custom.css" rel="stylesheet" />
  <!-- GOOGLE FONTS-->
  <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />

  <link href="css/ui.css" rel="stylesheet" />
  <link href="css/jquery-ui-1.10.3.custom.min.css" rel="stylesheet" />
  <link href="css/datepicker.css" rel="stylesheet" />
  <link href="css/datatable/datatable.css" rel="stylesheet" />

  <script src="js/jquery-1.10.2.js"></script>
  <script type='text/javascript' src='js/jquery/jquery-ui-1.10.1.custom.min.js'></script>
  <script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>

  <script src="js/dataTable/jquery.dataTables.min.js"></script>
  <!-- jsPDF library -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
  <!-- jsPDF AutoTable plugin -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>


</head>
<?php
include("php/header.php");
?>
<div id="page-wrapper">
  <div id="page-inner">
    <div class="row">
      <div class="col-md-12">
        <h1 class="page-head-line">Reporte Pagos

        </h1>

      </div>
    </div>






    <div class="row" style="margin-bottom:20px;">
      <div class="col-md-12">
        <fieldset class="scheduler-border">
          <legend class="scheduler-border">Búsqueda:</legend>
          <form class="form-inline" role="form" id="searchform">
            <div class="form-group">
              <label for="email">Nombre</label>
              <input type="text" class="form-control" id="student" name="student">
            </div>

            <div class="form-group">
              <label for="email"> Fecha de Ingreso </label>
              <input type="text" class="form-control" id="doj" name="doj">
            </div>

            <div class="form-group">
              <label for="email"> Carrera </label>
              <select class="form-control" id="career" name="career">
                <option value="">Selecciona Carrera</option>
                <?php
                $career = '';
                $sql = "select * from career where delete_status='0' order by career.career asc";
                $q = $conn->query($sql);

                while ($r = $q->fetch_assoc()) {
                  echo '<option value="' . $r['id'] . '"  ' . (($career == $r['id']) ? 'selected="selected"' : '') . '>' . $r['career'] . '</option>';
                }
                ?>
              </select>
            </div>

            <button type="button" class="btn btn-success btn-sm" id="find"> Buscar </button>
            <button type="reset" class="btn btn-danger btn-sm" id="clear"> Limpiar </button>
          </form>
        </fieldset>

      </div>
    </div>

    <script type="text/javascript">
      $(document).ready(function() {

        /*
        $('#doj').datepicker( {
                changeMonth: true,
                changeYear: true,
                showButtonPanel: false,
                dateFormat: 'mm/yy',
                onClose: function(dateText, inst) { 
                    $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
                }
            });
        	1353c-p function 18cp 
        */

        /******************/
        $("#doj").datepicker({

          changeMonth: true,
          changeYear: true,
          showButtonPanel: true,
          dateFormat: 'mm/yy',
          onClose: function(dateText, inst) {
            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            $(this).val($.datepicker.formatDate('MM yy', new Date(year, month, 1)));
          }
        });

        $("#doj").focus(function() {
          $(".ui-datepicker-calendar").hide();
          $("#ui-datepicker-div").position({
            my: "center top",
            at: "center bottom",
            of: $(this)
          });
        });

        /*****************/

        $('#student').autocomplete({
          source: function(request, response) {
            $.ajax({
              url: 'ajx.php',
              dataType: "json",
              data: {
                name_startsWith: request.term,
                type: 'report'
              },
              success: function(data) {

                response($.map(data, function(item) {

                  return {
                    label: item,
                    value: item
                  }
                }));
              }



            });
          }
          /*,
		      	autoFocus: true,
		      	minLength: 0,
                 select: function( event, ui ) {
						  var abc = ui.item.label.split("-");
						  //alert(abc[0]);
						   $("#student").val(abc[0]);
						   return false;

						  },
                 */



        });


        $('#find').click(function() {
          mydatatable();
        });


        $('#clear').click(function() {

          $('#searchform')[0].reset();
          mydatatable();
        });

        document.getElementById('printPdf').addEventListener('click', () => {

          // Obtener los datos del formulario formcontent
          var studentName = document.getElementById('studentName').textContent;
          var careerName = document.getElementById('careerName').textContent;
          var contact = document.getElementById('contact').textContent;
          var enrollmentDate = document.getElementById('enrollmentDate').textContent;
          var paymentTable = document.getElementById('formcontent').querySelector('#monthlyPaymentTable');

          // Crear un array para almacenar los datos de pagos
          var paymentsData = [];

          // Recorrer las filas de la tabla
          for (var i = 1; i < paymentTable.rows.length; i++) {
            var rowData = [];
            var cells = paymentTable.rows[i].cells;
            // Recorrer las celdas de cada fila
            for (var j = 0; j < cells.length; j++) {
              rowData.push(cells[j].textContent.trim()); // Agregar el contenido de la celda al array
            }
            paymentsData.push(rowData); // Agregar el array de datos de la fila al array principal
          }
          var totalAdeudado = document.getElementById('totalAdeudado').textContent.trim();
          var totalPagado = document.getElementById('totalPagado').textContent.trim();
          var balance = document.getElementById('saldo').textContent.trim();
          // Crear un nuevo documento jsPDF
          var doc = new jspdf.jsPDF('p', 'in', 'letter');

          // Establecer la fuente y el tamaño del texto para el título
          doc.setFont('helvetica', 'bold');
          doc.setFontSize(18);
          doc.text('Reporte de Pagos', doc.internal.pageSize.getWidth() / 2, 0.5, 'center');

          // Subtítulo "Información del Estudiante"
          doc.setFontSize(14);
          doc.text('Información del Estudiante', 0.5, 1);

          // Tabla con datos del estudiante
          var datosEstudiante = [
            ['Nombre', studentName, 'Carrera', careerName],
            ['Contacto', contact, 'Fecha de Ingreso', enrollmentDate]
          ];
          doc.autoTable({
            startY: 1.2,
            body: datosEstudiante,
            theme: 'grid',
            styles: {
              lineWidth: 0.01
            }
          });

          // Subtítulo "Información de Pagos"
          doc.setFontSize(14);
          doc.text('Información de Pagos', 0.5, doc.autoTable.previous.finalY + 0.5);

          doc.autoTable({
            startY: doc.autoTable.previous.finalY + 0.7,
            head: [
              ['Fecha', 'Pago', 'Observaciones']
            ],
            body: paymentsData,
            theme: 'grid',
            styles: {
              lineWidth: 0.01
            }
          });

          // Información de totales
          doc.setFontSize(12);
          doc.text('Total Adeudado:', 0.5, doc.autoTable.previous.finalY + 0.3);
          doc.text(totalAdeudado, 2, doc.autoTable.previous.finalY + 0.3);
          doc.text('Total Pagado:', 0.5, doc.autoTable.previous.finalY + 0.6);
          doc.text(totalPagado, 2, doc.autoTable.previous.finalY + 0.6);
          doc.text('Balance:', 0.5, doc.autoTable.previous.finalY + 0.9);
          doc.text(balance, 2, doc.autoTable.previous.finalY + 0.9);

          // Verificar si es necesario agregar una nueva página
          if (doc.autoTable.previous.finalY > 250) {
            doc.addPage();
          }

          // Guardar el archivo PDF
          doc.save('Reporte_de_Pagos.pdf');
        });
        // Function to convert number to words
        function convertNumberToWords(number) {
          // Convert number to words logic
          return "Quinientos"; // Replace with actual conversion logic
        }

        function mydatatable() {

          $("#subjectresult").html('<table class="table table-striped table-bordered table-hover" id="tSortable22"><thead><tr><th>Name/Contact</th><th>Fees</th><th>Balance</th><th>Career</th><th>DOJ</th><th>Action</th></tr></thead><tbody></tbody></table>');

          $("#tSortable22").dataTable({
            'sPaginationType': 'full_numbers',
            "bLengthChange": false,
            "bFilter": false,
            "bInfo": false,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': "datatable.php?" + $('#searchform').serialize() + "&type=report",
            'aoColumnDefs': [{
              'bSortable': false,
              'aTargets': [-1] /* 1st one, start by the right */
            }]
          });

        }

        ////////////////////////////
        $("#tSortable22").dataTable({

          'sPaginationType': 'full_numbers',
          "bLengthChange": false,
          "bFilter": false,
          "bInfo": false,

          'bProcessing': true,
          'bServerSide': true,
          'sAjaxSource': "datatable.php?type=report",

          'aoColumnDefs': [{
            'bSortable': false,
            'aTargets': [-1] /* 1st one, start by the right */
          }]
        });

        ///////////////////////////		



      });


      function GetFeeForm(sid) {

        $.ajax({
          type: 'post',
          url: 'getfeeform.php',
          data: {
            student: sid,
            req: '2'
          },
          success: function(data) {
            $('#formcontent').html(data);
            $("#myModal").modal({
              backdrop: "static"
            });
          }
        });


      }
    </script>




    <style>
      #doj .ui-datepicker-calendar {
        display: none;
      }
    </style>

    <div class="panel panel-default">
      <div class="panel-heading">
        Gestionar Reporte de Pagos
      </div>
      <div class="panel-body">
        <div class="table-sorting table-responsive" id="subjectresult">
          <table class="table table-striped table-bordered table-hover" id="tSortable22">
            <thead>
              <tr>

                <th>Nombre</th>
                <th>Pagos</th>
                <th>Balance</th>
                <th>Carrera</th>
                <th>Fecha de Ingreso</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>


    <!-------->

    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Reporte de Pagos</h4>
          </div>
          <div class="modal-body" id="formcontent">

          </div>
          <div class="modal-footer">
            <button id="printPdf" class="btn btn-primary">Imprimir PDF</button>
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>


    <!--------->


  </div>
  <!-- /. PAGE INNER  -->
</div>
<!-- /. PAGE WRAPPER  -->
</div>
<!-- /. WRAPPER  -->

<div id="footer-sec">
  CONTACTOS Y REFERENCIAS EN: <a href="https://www.facebook.com/people/Instituto-Del-Carmen-Cochabamba/100084527167834/" target="_blank"><i class="fa fa-facebook-square fa-2x" aria-hidden="true"></i> INSTITUTO DEL CARMEN</a>
</div>


<!-- BOOTSTRAP SCRIPTS -->
<script src="js/bootstrap.js"></script>
<!-- METISMENU SCRIPTS -->
<script src="js/jquery.metisMenu.js"></script>
<!-- CUSTOM SCRIPTS -->
<script src="js/custom1.js"></script>


</body>

</html>