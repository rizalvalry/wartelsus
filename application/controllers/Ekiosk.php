<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ekiosk extends CI_Controller {

    public function welcomeDevices() {
        $this->db->select('*');
        $this->db->from('t_branch_device');
        $this->db->join('t_branch', 't_branch.BranchID = t_branch_device.KODECABANG');
        $this->db->where('t_branch_device.KODECABANG', '6');
        $this->db->where('t_branch_device.IMEI', 'afe6f8186b9c373f');
    
        $query = $this->db->get();
        
            if ($query->num_rows() > 0) {
                $data = $query->result_array();
                header('Content-type: application/json');
                echo json_encode($data);
            } else {
                echo json_encode(array('message' => 'No data found'));
            }
        }

        public function memberDataShow() {

            $memberId = $_GET['idmember'];
            $query = "SELECT `LEVE_AMOUNT`,LEVE_REGISTRATIONNAME,LEVE_BRANCHID,LEVE_MEMBERID,LEVE_MEMBERNAME FROM `leve_member` WHERE `LEVE_MEMBERID` = '".$memberId."'";
            $hasil=$this->db->query($query);
    
            $data = $hasil->row_array();
            header('Content-type: application/json');
            echo json_encode($data);
        }

        public function historyCall() {
            $memberId = $_GET['idmember'];
            $query = "SELECT `TXDATE`, `TXTIME`, `EXT`, `COST`, TRK FROM `leve_datadb` WHERE `MEMBERID` = '".$memberId."'  ORDER BY `TXDATE` DESC LIMIT 5";
            $hasil=$this->db->query($query);
    
            $data = $hasil->result_array();
            header('Content-type: application/json');
            echo json_encode($data);
    
        }

        public function GETWartelSettings()
	{

			 $phoneimei = $_GET['phoneimei'];  //deviceid = phoneimei


			 $LEVE_USERID = 0;
			 $LEVE_LOGINNAME = "-";
			 $LEVE_USERID = 0;
			 $PULSE = 30;
			 $COST =  250;
			 $PULSE2 = 30;
			 $COST2 = 100;
			 $FREE1 = 10;
			 $FREE2 = 10;
			 $ESPIPADDRESS = '';

			 $query = "Select DEVICEID,LINKUSERID,KODECABANG,BranchName,NOTES,device_ip from v_deviceinfo where IMEI = '".$phoneimei."'";

			 $daynum = idate('w', time());
			 $timenow = date("H:i", time());
			 $currentTime = (int) date('Gis');

			 // 0 : Minggu 1: Senin 2: selasa 3: Rabu 4: Kamis 5: jumat 6: sabtu
			 // date("Y-m-d H:i:s", time()
			 $dayprom = Array(
				 "0" => "0", // Minggu
				 "1" => "0", // Senin
				 "2" => "0", // Selasa
				 "3" => "0", // Rabu
				 "4" => "0", // Kamis
				 "5" => "0", // Jumat
				 "6" => "0"  // Sabtu
			 );

			 $promtimest = Array(
				 "0" => "000000", // Minggu
				 "1" => "070000", // Senin
				 "2" => "140000", // Selasa
				 "3" => "070000", // Rabu
				 "4" => "070000", // Kamis
				 "5" => "000000", // Jumat
				 "6" => "070000"  // Sabtu
			 );

			 $promtimeend = Array(
				 "0" => "000000", // Minggu
				 "1" => "094059", // Senin
				 "2" => "170000", // Selasa
				 "3" => "094059", // Rabu
				 "4" => "094059", // Kamis
				 "5" => "000000", // Jumat
				 "6" => "094059"  // Sabtu
			 );



			 // var_dump($dayprom);

			 // echo $dayprom[$daynum];
			 // echo $timenow;




			 $hasil=$this->db->query($query);
			 $a = $hasil->result_array();

			 // //
			 if(count($a)>0){
					 // echo 'sini';
				 foreach( $a as $row)
				 {
						 $LEVE_USERID = $row['LINKUSERID'];
						 $LEVE_LOGINNAME = $row['BranchName'].'['.$row['KODECABANG']."]".':'.''.$row['NOTES']."";
						 $BranchID = $row['KODECABANG'];
						 $DeviceID = $row['DEVICEID'];
						 $ESPIPADDRESS = $row['device_ip'];
						 
				 }
			 }

			 $query2 = "Select PULSE1,COST1,FREE1,PULSE2,COST2,FREE2 from t_branch where BranchID = ".$BranchID;
			 $hasil2=$this->db->query($query2);
			 $b = $hasil2->result_array();

			 // //
			 // echo $rows;
			 if(count($b)>0){
					 // echo 'sini';
				 foreach( $b as $row)
				 {
						 $PULSE = $row['PULSE1'];
						 $COST = $row['COST1'];
						 $FREE1 = $row['FREE1'];
						 $PULSE2 = $row['PULSE2'];
						 $COST2 = $row['COST2'];
						 $FREE2 = $row['FREE2'];
				 }
			 }



			 if($dayprom[$daynum]==1){

				 if ($currentTime > $promtimest[$daynum] && $currentTime < $promtimeend[$daynum] )
				 {
						 $stat="Berlaku";
						 $COST2=0;
				 }
				 else
				 {
						 $stat="Expired";
				 }
				 // echo "Hari Promo " ."- ". $stat;
			 }else{
				 // echo "Hari Normal";
			 }
			 // die;

			 $item = Array(
					"rc" => "00" ,// No member tidak ditemukan atau tidak aktif
					"userid" => $LEVE_USERID,
					"branchid" => $BranchID,
					"deviceid" => $DeviceID,
					"loginname" => $LEVE_LOGINNAME,
					"pulse" => $PULSE,
					"cost" => intval($COST),
					"pulse2" => $PULSE2,
					"cost2" => intval($COST2),
					"free1" => $FREE1,
					"free2" => $FREE2,
					"esp_ipaddress" => $ESPIPADDRESS
					
				);
				$itemList[0] = $item;
				// die;
			 $data = json_encode($itemList[0]);
			 // echo '({"total":"' . $rows . '","results":' . $data . '})';
			 header('Content-type: application/json');
			 // echo json_encode($itemList);;
			//  echo '({"results":[' . $data . ']})';
			 echo $data;

	}

    public function GETPinValidation()
	{
	   		 $POSResponseId = 0;

			 $idmember = $_GET['idmember'];
			 $pin = $_GET['pin'];
			 $branchid = $_GET['branchid'];

			 $query = "Select LEVE_PIN,LEVE_STATUS,LEVE_REGISTRATIONNAME,LEVE_BRANCHID from leve_member where LEVE_MEMBERID=".$idmember. ' and LEVE_BRANCHID ='.$branchid;
			 
			 $rsData = $this->db->query($query);

			 $a = $rsData->result_array();
			 // //
			 // echo $rows;
			 if(count($a)>0){
					 // echo 'sini';

				 foreach( $a as $row)
				 {
						 $PIN = $row['LEVE_PIN'];
						 $STATUS = $row['LEVE_STATUS'];
						 $USERBRANCHID = $row['LEVE_BRANCHID'];
						 $LEVE_MEMBERNAME = substr($row['LEVE_REGISTRATIONNAME'],0,20);

				 }

				 if($PIN==$pin){
					 if($STATUS<>0){
						 if($USERBRANCHID == $branchid){
					 	 			$item = Array(
												 "rc" => "00" ,// No member tidak ditemukan atau tidak aktif
												 "response" => "TRUE",
												 "description" => "Validasi Berhasil",
												 "name" => $LEVE_MEMBERNAME
												);
							}else{
								$item = Array(
															"rc" => "16" , // beda branch
															"response" => "false",
															"description" => "Status Member Tidak terdaftar ",
															"name" => $LEVE_MEMBERNAME
														 );

							}
						}else{
							$item = Array(
														"rc" => "16" ,// No member tidak ditemukan atau tidak aktif
														"response" => "false",
														"description" => "Status Member Tidak Aktif " . $LEVE_MEMBERNAME,
														"name" => $LEVE_MEMBERNAME
													 );
						}
				 }else{
					 $item = Array(
												 "rc" => "15" ,//
												 "response" => "false",
												 "description" => "Pin Tidak Valid ".$LEVE_MEMBERNAME,
												 "name" => $LEVE_MEMBERNAME

												);
				 }

					$itemList[0] = $item;
			 }else{

				 $item = Array(
													 "rc" => "14" ,//
													 "response" => "false",
													 "description" => "Data Tidak Ditemukan",
													 "name" => "-"
											);
					$itemList[0] = $item;
			 }

			 $data = json_encode($itemList[0]);
			 // echo '({"total":"' . $rows . '","results":' . $data . '})';
			 header('Content-type: application/json');
			 // echo json_encode($itemList);;
			//  echo '({"results":[' . $data . ']})';
			 echo $data;

	}

	public function PostWartelResults()
	{
			 // $memberid = $_POST['memberid'];
			 // $db2 = $this->load->database('fbrid', TRUE);
			 // $data = json_decode(file_get_contents('php://input'), true);
				// print_r($data);
			 // echo $data["operacion"];

			 // echo $data['memberid'];

			 $branchid = $_GET['branchid'];
			 $deviceid = $_GET['deviceid'];

			 $POSResponseId = 0;
			 $trx_code = '02';

			 $reff_number = $_GET['reff_number'];
			 $debitamount=$_GET['amount'];
			 $idmember = $_GET['idmember'];
			 $datetime=$_GET['datetime'];
			 $TXDATE =$_GET['TXDATE'];
			 $TXTIME =$_GET['TXTIME'];
			 $TRK =$_GET['TRK'];
			 $EXT =$_GET['EXT'];
			 $NAME =$_GET['NAME'];
			 $DIAL =$_GET['DIAL'];
			 $DURSTR =$_GET['DURSTR'];
			 $COST =$_GET['COST'];
			 $DURATION =$_GET['DURATION'];
			 $PULSE =$_GET['PULSE'];

			 // $queryInsertDataDB = "INSERT INTO LEVE_DATADB (, , , , , , , , , STATUS, DIVISION, , DURATION, EXTHIDE, DIALCODE, , RATEMUL, ISADMIN, , TR, ACCCODE, RING)" ;
			 // $queryInsertDataDB = $queryInsertDataDB ." VALUES ('" .$TXDATE ."','". $TXTIME ."','". $TRK ."','". $EXT ."','". $NAME ."','". $DIAL ."','-','". $DURSTR ."',". $COST .",'P', 'U', ".$branchid.", " .$DURATION
			 // $queryInsertDataDB = $queryInsertDataDB .", 0, 3, " .$PULSE. ", NULL, NULL, ".$deviceid.", NULL, NULL, NULL)";
			 $NAME = substr($NAME,0,17);

			 $item_data = array(
				'TXDATE' => $TXDATE,
 				'TXTIME' => $TXTIME,
 				'TRK' => $TRK,
 				'EXT' => $EXT,
				'MEMBERID' => $idmember,
 				'NAME' => $NAME,
 				'DIAL' => $DIAL,
				'DURSTR' => $DURSTR,
				'COST' => $COST,
				'BRANCHID' => $branchid,
				'DURATION' => $DURATION,
				'PULSE' => $PULSE,
				'DEVICEID' => $deviceid,
				'USERID' => $reff_number
			);

			$dbinsert = $this->db;
			$dbinsert->insert('leve_datadb', $item_data);
			if($dbinsert->affected_rows() == 1) {
					$transid = $dbinsert->insert_id();
			} else {
					$transid = 'null';
			}

			if($transid=='null'){
				$item = Array(
										 "rc" => "13" ,// No member tidak ditemukan atau tidak aktif
										 "balance" => 0
										);
				$itemList[0] = $item;
			}else{

				if (($debitamount!=0) && ($trx_code=='02')){
					$query = "Select LEVE_REGISTRATIONNAME,LEVE_AMOUNT from leve_member where LEVE_MEMBERID=".$idmember." and LEVE_BRANCHID=".$branchid;
	 			 	$hasil=$this->db->query($query);
	 			 	$a = $hasil->result_array();
	 			 if(count($a)>0){
	 					 // echo 'sini';
	 				 foreach( $a as $row)
	 				 {
						 		$MEMBERNAME = $row['LEVE_REGISTRATIONNAME'];
	 							$CLOSEBALANCE = $row['LEVE_AMOUNT'];

	 				 }
					$item = Array(
												"POSResponseId" =>$transid,
												"trx_code" => $trx_code,
												"amount" => $debitamount+0,
												"idmember"=> $idmember,
												"balance" => $CLOSEBALANCE+$debitamount+0,
												"closebalance" => $CLOSEBALANCE+0,
												"member"=> $MEMBERNAME,
												"reff_number" => $reff_number,
												"datetime" => date("Y-m-d H:i:s", time()),
												"rc" => "00"
											 );
					$itemList[0] = $item;
				}else{
					$item = Array(
 											 "rc" => "14" ,// No member tidak ditemukan atau tidak aktif
 											 "balance" => 0
 											);
 					$itemList[0] = $item;

				}
			}
		}
			 $data = json_encode($itemList[0]);
			 // echo '({"total":"' . $rows . '","results":' . $data . '})';
			 header('Content-type: application/json');
			 // echo json_encode($itemList);;
			//  echo '({"results":[' . $data . ']})';
			 echo $data;


	}




}