<script src="jquery/printarea/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>
<script src="jquery/printElement/jquery.printElement.js" type="text/JavaScript" language="javascript"></script>
<script src="http://code.jquery.com/jquery-migrate-1.0.0.js"></script>
<?php

session_start();

if(isset($_REQUEST["barcodes"]) && $_REQUEST["barcodes"] != "") {
    // array()
    $barcodes = $_REQUEST["barcodes"];
    $tmp = "";
    for($i = 0; $i < count($barcodes); $i++) {
        $tmp .= $barcodes[$i];
    }
}
else {
    // Error
}

if(isset($_REQUEST["print_pocket_label"]) && $_REQUEST["print_pocket_label"] != "") {
    // array()
    $print_pocket_label = $_REQUEST["print_pocket_label"];
}
else {
    // Error
}

if (isset($_REQUEST["label_start"]) && is_numeric($_REQUEST["label_start"]) && $_REQUEST["label_start"] > 0 && $_REQUEST["label_start"] < 9) {
    $label_start = $_REQUEST["label_start"];
}

$barcode_count = count($barcodes);

$_SESSION['saved_form'] = $_REQUEST;

$print_array = array();

include_once('../lib/quicklabels.php');

//Store all spine labels and pocket label counterparts in new, combined array
for($x = 0; $x < $barcode_count; $x++) {
    if ($barcodes[$x] != "") {
        $print_array[] = quicklabels($barcodes[$x], $print_pocket_label[$x]);
    }
}

// Add empty rows to print_array to start print on specified label
if (isset($label_start) && $label_start != "") {
    for ($x = 1; $x < $label_start; $x++) {
        array_unshift($print_array, array("&nbsp;", "&nbsp;"));
    }
}

$label_row = "";
$label_page = "";
//Build labels using divs
for ($x = 0; $x < count($print_array); $x++) {
        $label_row .= "<div class=\"label_container\">\n";
        if (isset($print_array[$x][0]) && $print_array[$x][0] != "") {
            $cnumval = $print_array[$x][0];
        } else {
            $cnumval = "&nbsp;";
        }
        if (isset($print_array[$x][1]) && $print_array[$x][1] != "") {
            $pocketval = $print_array[$x][1];
        } else {
            $pocketval = "&nbsp;";
        }        
        $label_row .= "<div class=\"cnum\"><div>{$cnumval}</div></div>\n<div class=\"pocket\"><div>{$pocketval}</div></div>\n";

    $label_row .= "</div>\n";
    if ($x % 2 == 0) {
      $label_row .= '<div class="middle_padding">&nbsp</div>';
    }
    //$label_row .= "<tr>\n<td class=\"spacer_cell_vert\" colspan=\"7\">&nbsp;</td>\n</tr>\n";
    // Test whether we have eight labels for 2-col printing, or are on the last label
    if ((($x + 1) % 8 == 0) || $x + 1 >= count($print_array)) {
        $label_page .= "<div class=\"label_page\">{$label_row}</div>\n";
        $label_row = "";
    }
}


function makeTitleArray($input) {
    $title = explode("<br />", $input);
    $return = array();
    for ($x = 0; $x < count($title); $x++) {
        if (isset($title[$x]) && $title[$x] != "") {
            $title_split = explode("\n", wordwrap($title[$x], 30, "\n"));
            $return = array_merge($return, $title_split);
        }
    }
    return $return;
}


?>
<!-- Radio list of different printers to choose from -->
<div id="link-area" class="print_label_button">
    <div id="link-print">
        <div class="button_left_div"><a id="print_button" href="#print"><img src="images/icon-print.png" /><br/>Print Labels</a>
        </div>
        <div class="button_right_div">
            <input type="radio" name="printer" id="printer1" value="dot_matrix" /> Okidata Dot Matrix<br />
            <input type="radio" name="printer" id="printer2" value="laser" /> Laser Printer<br />
            <input type="radio" name="printer" id="printer3" value="dymo" /> Dymo Printer
        </div>
        <div class="clear"></div>
    </div>
</div>

<script>
$(document).ready(function(){
  // Sets preview appearance for each printer type. This view does not affect actual printing, which is all css.
  $('input[name="printer"]').click(function(){
  $('.pocket').css("display", "block");
  switch($(this).val()) {
    case "dot_matrix":
      $('#table_div').css("width", 400);
      break;
    case "laser":
      $('#table_div').css("width", 800);
      break;
    case "dymo":
      $('#table_div').css("width", 100);
      $('.pocket').css("display", "none");
      break;
  }
  });
});
</script>

<?php
    print "<div id=\"table_div\">$label_page</div>";
?>

<script>
    $("a#print_button").click(function(e){
        e.preventDefault();
        printer_css = $("input[name=printer]:checked").val();
        if (typeof printer_css !== "undefined") {
          //Grab appropriate browser for laser printing
          if (printer_css == "laser") {
            if (navigator.userAgent.match(/trident/i))
              printer_css += "_ie";
            else if (navigator.userAgent.match(/firefox/i))
              printer_css += "_firefox";
            else if (navigator.userAgent.match(/chrome/i))
              printer_css += "_chrome";
          }
          $("#table_div").printArea( { mode: "popup", retainAttr: [], extraCss: 'css/'+printer_css+'.css' } );
        } else {
          alert("Type of printer must be selected.");
        }

    });
</script>