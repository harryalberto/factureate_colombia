<?php
class PDF extends FPDF
{
    // Pie de página
    function Footer()
    {
        // Posición: a 1,5 cm del final
        //$this->SetY(-15);
        // Arial italic 8
        //$this->SetFont('Arial','I',8);
        // Número de página
        //$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
        // Cargar los datos
    function LoadData($file)
    {
        // Leer las líneas del fichero
        $lines = file($file);
        $data = array();
        foreach($lines as $line)
            $data[] = explode(';',trim($line));
        return $data;
    }

    // Tabla simple
    function BasicTable($header, $data)
    {
        // Cabecera
        foreach($header as $col)
            $this->Cell(40,7,$col,1);
        $this->Ln();
        // Datos
        foreach($data as $row)
        {
            foreach($row as $col)
                $this->Cell(40,6,$col,1);
            $this->Ln();
        }
    }

    // Una tabla más completa
    function ImprovedTable($header, $data)
    {
        // Anchuras de las columnas
        $w = array(40, 35, 45, 40);
        // Cabeceras
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C');
        $this->Ln();
        // Datos
        foreach($data as $row)
        {
            $this->Cell($w[0],6,$row[0],'LR');
            $this->Cell($w[1],6,$row[1],'LR');
            $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R');
            $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R');
            $this->Ln();
        }
        // Línea de cierre
        $this->Cell(array_sum($w),0,'','T');
    }

    // Tabla coloreada
    function FancyTable($header, $data)
    {
        // Colores, ancho de línea y fuente en negrita
        $this->SetFillColor(255,0,0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('','B');
        // Cabecera
        $w = array(40, 35, 45, 40);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
        $this->Ln();
        // Restauración de colores y fuentes
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Datos
        $fill = false;
        foreach($data as $row)
        {
            $this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
            $this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
            $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R',$fill);
            $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);
            $this->Ln();
            $fill = !$fill;
        }
        // Línea de cierre
        $this->Cell(array_sum($w),0,'','T');
    }

    function FancyTableNew($header, $data, $orientacion, $tamanos_w)
    {
        // Colores, ancho de línea y fuente en negrita
        $this->SetFillColor(4,26,113);
        $this->SetTextColor(255);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('','B');
        // Cabecera
        //$w = array(40, 35, 45, 40);

        /*for($i=0;$i<count($header);$i++)
            $this->Cell($tamanos_w[$i],5,$header[$i],1,0,'C',true);
        $this->Ln();*/

        // Cabecera- verifico si tiene doble linea
        $doble = 0;
        $segunda_linea = array();

        for($i=0;$i<count($header);$i++){
            if (strlen($header[$i]) > ($tamanos_w[$i] / 2.5)) $doble = 1;
        }

        // Cabecera - pinto la cabecera
        for($i=0;$i<count($header);$i++){
            if (strlen($header[$i]) > ($tamanos_w[$i] / 2.5)){
                $palabras = explode(" ", $header[$i]);

                if (count($palabras) > 1){
                    $this->Cell($tamanos_w[$i],5,$palabras[0],1,0,'C',true);
                    $segunda_linea[$i] = $palabras[1];
                } else {
                    $this->Cell($tamanos_w[$i],5,$header[$i],1,0,'C',true);
                    $segunda_linea[$i] = '';
                }
            } else {
                $this->Cell($tamanos_w[$i],5,$header[$i],1,0,'C',true);

                if ($doble == 1) $segunda_linea[$i] = '';
            }
        }
        $this->Ln();

        if ($doble == 1){
            for ($i=0; $i<count($segunda_linea); $i++){
                $this->Cell($tamanos_w[$i],5,$segunda_linea[$i],1,0,'C',true);
            }

            $this->Ln();
        }

        // Restauración de colores y fuentes
        $this->SetFillColor(204,204,204);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Datos
        $fill = false;
        $columnas = count($header);

        for ($i=0; $i<count($data); $i++){
            for ($z=0; $z<$columnas; $z++){
                $this->Cell($tamanos_w[$z],6,$data[$i][$z],'LR',0,$orientacion[$z],$fill);
                //$this->MultiCell($tamanos_w[$z],6,$data[$i][$z],'LR',0,$orientacion[$z],$fill);
            }
            
            $this->Ln();
            $fill = !$fill;
        }
        
        // Línea de cierre
        $this->Cell(array_sum($tamanos_w),0,'','T');
    }
}
?>