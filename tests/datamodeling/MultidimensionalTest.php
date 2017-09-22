<?php

use Francerz\PhpModel\Multidimensional;
use Francerz\PhpModel\Collection;
use PHPUnit\Framework\TestCase;

class MultidimensionalTest extends TestCase
{
    private $data = array(
        array('id_ctl_gasto'=>1, 'fecha'=>'2017-09-04', 'total'=>7000),
        array('id_ctl_gasto'=>1, 'fecha'=>'2017-09-01', 'total'=>3000),
        array('id_ctl_gasto'=>1, 'fecha'=>'2017-08-31', 'total'=>1000),
        array('id_ctl_gasto'=>1, 'fecha'=>'2017-08-27', 'total'=>2000),
        array('id_ctl_gasto'=>2, 'fecha'=>'2017-09-01', 'total'=>500),
        array('id_ctl_gasto'=>2, 'fecha'=>'2017-08-27', 'total'=>800),
        array('id_ctl_gasto'=>2, 'fecha'=>'2017-08-25', 'total'=>300),
        array('id_ctl_gasto'=>1, 'fecha'=>'2017-08-31', 'total'=>300)
    );

    private $alumnos = array(
        ['semestre'=>'1', 'genero'=>'M', 'modalidad'=>'Escolarizada', 'cantidad'=>16, 'carrera'=>'Informática'],
        ['semestre'=>'1', 'genero'=>'F', 'modalidad'=>'Escolarizada', 'cantidad'=> 5, 'carrera'=>'Informática'],
        ['semestre'=>'3', 'genero'=>'M', 'modalidad'=>'Escolarizada', 'cantidad'=>14, 'carrera'=>'Informática'],
        ['semestre'=>'3', 'genero'=>'F', 'modalidad'=>'Escolarizada', 'cantidad'=> 5, 'carrera'=>'Informática'],
        ['semestre'=>'3', 'genero'=>'M', 'modalidad'=>'Escolarizada', 'cantidad'=> 9, 'carrera'=>'Informática'],
        ['semestre'=>'3', 'genero'=>'F', 'modalidad'=>'Escolarizada', 'cantidad'=> 4, 'carrera'=>'Informática'],
        ['semestre'=>'5', 'genero'=>'M', 'modalidad'=>'Escolarizada', 'cantidad'=>11, 'carrera'=>'Informática'],
        ['semestre'=>'5', 'genero'=>'F', 'modalidad'=>'Escolarizada', 'cantidad'=> 4, 'carrera'=>'Informática'],
        ['semestre'=>'7', 'genero'=>'M', 'modalidad'=>'Escolarizada', 'cantidad'=> 9, 'carrera'=>'Informática'],
        ['semestre'=>'5', 'genero'=>'F', 'modalidad'=>'Escolarizada', 'cantidad'=> 5, 'carrera'=>'Informática'],
        ['semestre'=>'7', 'genero'=>'M', 'modalidad'=>'Escolarizada', 'cantidad'=> 8, 'carrera'=>'Informática'],
        ['semestre'=>'7', 'genero'=>'F', 'modalidad'=>'Escolarizada', 'cantidad'=> 4, 'carrera'=>'Informática'],
        ['semestre'=>'1', 'genero'=>'M', 'modalidad'=>'Escolarizada', 'cantidad'=>25, 'carrera'=>'Sistemas'],
        ['semestre'=>'1', 'genero'=>'F', 'modalidad'=>'Escolarizada', 'cantidad'=> 9, 'carrera'=>'Sistemas'],
        ['semestre'=>'1', 'genero'=>'M', 'modalidad'=>'A Distancia' , 'cantidad'=> 8, 'carrera'=>'Sistemas'],
        ['semestre'=>'1', 'genero'=>'F', 'modalidad'=>'A Distancia' , 'cantidad'=> 2, 'carrera'=>'Sistemas'],
    );

    public function testInstantiateFromArray()
    {
        $matrix = new Multidimensional($this->data,['id_ctl_gasto','fecha']);

        $fechas = $matrix->getDimensionValues('fecha');
        sort($fechas);
        $this->assertEquals(array(
                '2017-08-25',
                '2017-08-27',
                '2017-08-31',
                '2017-09-01',
                '2017-09-04'
            ), $fechas);

        $gastos = $matrix->getDimensionValues('id_ctl_gasto');
        sort($gastos);

        $this->assertEquals([1,2], $gastos);

        return $matrix;
    }
    public function getCellTestCases()
    {
        return array(
            array(['fecha'=>'2017-09-01'], 3500, 2),
            array(['fecha'=>'2017-09-04'], 7000, 1),
            array(['fecha'=>'2016-06-02'], 0, 0),
            array(['id_ctl_gasto'=>1], 13300, 5),
            array(['id_ctl_gasto'=>5], 0, 0),
            array(['fecha'=>'2017-08-31','id_ctl_gasto'=>1], 1300, 2),
            array(['fecha'=>['2017-09-01','2017-09-04']], 10500, 3),
            array([],14900,8)
        );
    }

    /**
     * @depends testInstantiateFromArray
     * @dataProvider getCellTestCases
     * @test
     */
    public function testGetCell($coords, $exSum, $exCount, $matrix)
    {
        $cell = $matrix->getCell(0,$coords,'array_sum','total');
        $this->assertEquals($exSum, $cell);
        
        $cell = $matrix->getCell(0,$coords,'count');
        $this->assertEquals($exCount, $cell);
        
        $cell = $matrix->getCell('alpha',$coords, function($vals){
            $suma = 0;
            foreach($vals as $val) {
                $suma += $val;
            }
            return $suma / count($vals);
        },'total');
        // var_dump($coords, $cell);
    }

    public function testInstantiateFromCollection()
    {
        $dataCollection = new Collection($this->data);
        $matrix = new Multidimensional($dataCollection, ['id_ctl_gasto','fecha']);
        
        $fechas = $matrix->getDimensionValues('fecha');
        sort($fechas);
        $this->assertEquals(array(
                '2017-08-25',
                '2017-08-27',
                '2017-08-31',
                '2017-09-01',
                '2017-09-04'
            ), $fechas);

        $gastos = $matrix->getDimensionValues('id_ctl_gasto');
        sort($gastos);

        $this->assertEquals([1,2], $gastos);

        return $matrix;
    }
    
    /**
     * @depends testInstantiateFromCollection
     * @dataProvider getCellTestCases
     * @test
     */
    public function testGetCellFromCollection($coords, $exSum, $exCount, $matrix)
    {
        $cell = $matrix->getCell(0,$coords,'array_sum','total');
        $this->assertEquals($exSum, $cell);

        $cell = $matrix->getCell(0,$coords,'count');
        $this->assertEquals($exCount, $cell);
    }

    public function getDimensionValuesWithFilterTestCases()
    {
        return array(
            array('fecha',['id_ctl_gasto'=>2],['2017-09-01','2017-08-27','2017-08-25']),
            array('id_ctl_gasto',['fecha'=>'2017-08-31'],[1]),
            array('id_ctl_gasto',[],[1,2])
        );
    }

    /**
     * @depends testInstantiateFromCollection
     * @dataProvider getDimensionValuesWithFilterTestCases
     * @test
     */
    public function getDimensionValuesWithFilterFromCollection($col, $filter, $exVals, $matrix)
    {
        $vals = $matrix->getDimensionValues($col, $filter);
        $this->assertEquals($exVals, $vals);
    }
}