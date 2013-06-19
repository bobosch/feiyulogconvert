<?php
class feiyu {
/* Feiyu Log
http://www.rcgroups.com/forums/showthread.php?t=1664005

#DATA1
#DATA1,?,?,?,?,?,Lat1,Lat2,Lat3,Lat4,Long1,Long2,Long3,long4,CutHDG1,CutHDG2,?,?,altitude1,altitude2 ,airspeed1,airspeed2,barualt1,barualt2,tgtdist1,tg tdist2,latost1,latost2,?,?

?        :
?        :
?        :
?        :
?        :
Lat1     : See Coordinates Below 
Lat2     : See Coordinates Below
Lat3     : See Coordinates Below
Lat4     : See Coordinates Below
Long1    : See Coordinates Below
Long2    : See Coordinates Below
Long3    : See Coordinates Below
Long4    : See Coordinates Below
CutHDG1  : x*2.56
CutHDG2  : x*0.01 (added to Cut HDG)
?        :
?        :
Altitude1: x*25.6
Altitude2: x*0.1 (Added to Altitude1)
Airspeed1: x*90 (2 or 3 add 3.6, above 4 90+((x-1)*3.6)
Airspeed2: x*0.36 added to airspeed1
Barualt1 : x*25.6 (If above 6000m subtract 6000 and negative sign the result for below 0 altitude.)
Barualt2 : x*0.1 added to barualt1
TgtDist1 : x*256
TgtDist2 : x*1
LatOST1  : x*25.6 (Up to 127, 128+ = (x-128)*-value)
LatOST2  : x*0.1 (if lastost1 is - then subtract, else add)
?        :
?        :

#DATA2
#DATA2,?,?,Waypoint,TgtHDG1,TgtHDG2,TgtAlt1,TgtAlt 2,TgtGS1,TgtGS2,HomeLat1,HomeLat2,HomeLat3,HomeLat 4,HomeLng1,HomeLng2,HomeLng3,HomeLng4,TgtLat1,TgtL at2,TgtLat3,TgtLat4,TgtLng1,TgtLng2,TgtLng3,TgtLng 4,APVlt1,ApVlt2,BattVlt,Hour,Minute,Second,Month,D ay,?,temp,BattCur1,BattCur2,?,?,refresh,?,Downlink Refresh,?,?

?              :
?              :
Waypoint       : x*1
TgtHDG1        : x*256
TgtHDG2        : x added to TgtHDG1
TgtAlt1        : x*256
TgtAlt2        : x added to TgtAlt1
TgtGS1         : x*92.16
TgtGS2         : x*0.36 added to TgtGS1
HomeLat1       : See Coordinates Below
HomeLat2       : See Coordinates Below
HomeLat3       : See Coordinates Below
HomeLat4       : See Coordinates Below
HomeLng1       : See Coordinates Below
HomeLng2       : See Coordinates Below
HomeLng3       : See Coordinates Below
HomeLng4       : See Coordinates Below
TgtLat1        : See Coordinates Below
TgtLat2        : See Coordinates Below
TgtLat3        : See Coordinates Below
TgtLat4        : See Coordinates Below
TgtLng1        : See Coordinates Below
TgtLng2        : See Coordinates Below
TgtLng3        : See Coordinates Below
TgtLng4        : See Coordinates Below
APVlt1         : x*25.6
APVlt2         : x*0.1 added to APVlt1
BattVlt        : x*0.1
Hour           : x*1
Minute         : x*1
Second         : x*1
Month          : x*1
Day            : x*1
?              :
Temp           : x*1
BattCur1       : x*25.6
BattCur2       : x*0.1 added to BattCur1
?              :
?              :
Refresh        : x*1
?              :
DownlinkRefresh: x*1
?              :
?              :

#DATA3
#DATA3,?,?,Pitch1,Pitch2,roll1,roll2,AileronGain1, AileronGain2,ElevatorGain1,ElevatorGain2,ThrottleG ain1,ThrottleGain2,RudderGain1,RudderGain2,AltErro r1,AltError2,?,?

?            :
?            :
Pitch1       : x*25.6 *WRONG*
Pitch2       : x*0.1 (added to pitch1)
Roll1        : x*12.3 
Roll2        : x*0.1 (Added to Roll1)
AileronGain1 :
AileronGain2 :
ElevatorGain1:
ElevatorGain2:
ThrottleGain1:
ThrottleGain2:
RudderGain1  :
RudderGain2  :
AltError1    : x*256
AltError2    : x*1
?            :
?            :

Coord1: x*4.26666666666667 (up to 128, 128+ (x-128)*-value) 
Coord2: x*0.0166666666666667 (if Coord1 is - then subtract, else add)
Coord3: x*0.000426666666666667 (added to Coord1)
Coord4: x*0.00000166666666666667 (added to Coord1)
*/

/* KML
https://developers.google.com/kml/documentation/kmlreference
https://developers.google.com/kml/documentation/touring

<heading>
    Direction (azimuth) of the camera, in degrees. Default=0 (true North). (See diagram.) Values range from 0 to 360 degrees. 
<tilt>
    Rotation, in degrees, of the camera around the X axis. A value of 0 indicates that the view is aimed straight down toward the earth (the most common case). A value for 90 for <tilt> indicates that the view is aimed toward the horizon. Values greater than 90 indicate that the view is pointed up into the sky. Values for <tilt> are clamped at +180 degrees. 
<roll>
    Rotation, in degrees, of the camera around the Z axis. Values range from −180 to +180 degrees. 
*/

	public $altitude=0;
	public $latitude=0;
	public $longitude=0;

	public $pitch=0;
	public $roll=0;
	public $yaw=0; // heading

	public $hour=0;
	public $minute=0;
	public $second=0;
	public $millisecond=0;

	public $errors=array();
	public $output='';

	protected $data=array();
	protected $format='kml';
	protected $interval=100;
	protected $last;
	protected $temp;

	public function convert($filename){
		$file=fopen($filename,'r');
		$f=strtoupper($this->format);

		$method='get'.$f.'start';
		if(method_exists($this,$method)) $this->output=$this->$method();

		$method='get'.$f.'line';
		if(method_exists($this,$method)){
			while($this->loadFeiyuLog($file)){
				$this->decodeFeiyuLog();
				$this->output.=$this->$method();
			}
		}

		$method='get'.$f.'end';
		if(method_exists($this,$method)) $this->output=$this->$method();

		fclose($file);
	}

	public function getOutputFormat(){
		return $this->format;
	}

	public function setOutputFormat($format){
		if(in_array($format,array('ass','csv','kml'))) $this->format=$format;
	}

	protected function loadFeiyuLog($file){
		do{
			$load=false;
			$csv=fgetcsv($file);
			if($csv){
				switch($csv[0]){
					case '#DATA1':
						$this->data[1]=$csv;
						break;
					case '#DATA2':
						$load=true;
						$this->data[2]=$csv;
						break;
					case '#DATA3':
						$load=true;
						$this->data[3]=$csv;
						break;
				}
			}
		}while($load);
		return $csv ? true : false;
	}

	protected function decodeFeiyuLog(){
		$d=$this->data;

		if($d[1][2]!=241) $this->errors[]='Unknown data format';
		
		$this->latitude=$this->getCoordinate($d[1][6],$d[1][7],$d[1][8],$d[1][9]);
		$this->longitude=$this->getCoordinate($d[1][10],$d[1][11],$d[1][12],$d[1][13]);
		$this->altitude=$this->getValue($d[1][18],$d[1][19],0.1);

		$this->yaw=$this->getValue($d[1][14],$d[1][15],0.01);
		$this->hour=$this->num($d[2][29],23);
		$this->minute=$this->num($d[2][30],59);
		$s=$this->num($d[2][31],59);
		if($s==$this->second){
			$this->millisecond+=$this->interval;
		}else{
			$this->second=$s;
			$this->millisecond=0;
		}

		$this->pitch=$this->getValue($d[3][3],$d[3][4],0.1,true);
		$this->roll=$this->getValue($d[3][5],$d[3][6],0.1,true);
	}

	function getCoordinate($coord1,$coord2,$coord3,$coord4){
		$coord1=$this->tinyint($coord1);
		$coord2=$this->tinyint($coord2);
		$coord3=$this->tinyint($coord3);
		$coord4=$this->tinyint($coord4);

		if($coord1>127){
			$coord1=-($coord1-128);
			$coord2=-$coord2;
			$coord3=-$coord3;
			$coord4=-$coord4;
		}

		return $coord1*4.26666666666667+$coord2*0.0166666666666667+$coord3*0.000426666666666667+$coord4*0.00000166666666666667;
	}

	function getValue($a,$b,$f=1,$neg=false){
		$a=$this->tinyint($a);
		$b=$this->tinyint($b);

		if($neg && $a>127){
			$a=-($a-128);
			$b=-$b;
		}
		
		return ($a*256+$b)*$f;
	}

	function tinyint($value){
		return $this->num($value,255,0);
	}

	function num($value,$max=255,$min=0){
		return min(max(intval($value),$min),$max);
	}

	protected function getCSVstart(){
		return "#DATA1,?,?,?,?,?,Lat1,Lat2,Lat3,Lat4,Long1,Long2,Long3,long4,CutHDG1,CutHDG2,?,?,altitude1,altitude2 ,airspeed1,airspeed2,barualt1,barualt2,tgtdist1,tg tdist2,latost1,latost2,?,?,".
			"#DATA2,?,?,Waypoint,TgtHDG1,TgtHDG2,TgtAlt1,TgtAlt 2,TgtGS1,TgtGS2,HomeLat1,HomeLat2,HomeLat3,HomeLat 4,HomeLng1,HomeLng2,HomeLng3,HomeLng4,TgtLat1,TgtL at2,TgtLat3,TgtLat4,TgtLng1,TgtLng2,TgtLng3,TgtLng 4,APVlt1,ApVlt2,BattVlt,Hour,Minute,Second,Month,D ay,?,temp,BattCur1,BattCur2,?,?,refresh,?,Downlink Refresh,?,?,".
			"#DATA3,?,?,Pitch1,Pitch2,roll1,roll2,AileronGain1,AileronGain2,ElevatorGain1,ElevatorGain2,ThrottleG ain1,ThrottleGain2,RudderGain1,RudderGain2,AltErro r1,AltError2,?,?,".
			"hour,minute,second,millisecond,longitude,latitude,yaw,pitch,roll".
			"\n";
	}

	protected function getCSVline(){
		return implode(',',$this->data[1]).','.implode(',',$this->data[2]).','.implode(',',$this->data[3]).','.$this->hour.','.$this->minute.','.$this->second.','.$this->millisecond.','.$this->longitude.','.$this->latitude.','.$this->yaw.','.$this->pitch.','.$this->roll."\n";
	}

	protected function getKMLline(){
		if($this->longitude){
// 				$sxe->Document->Folder->Placemark->{'Track'}->addChild('coord',$this->longitude.' '.$this->latitude.' '.$this->altitude);
			$this->temp['coord'][]=$this->longitude.' '.$this->latitude.' '.$this->altitude;
			$time=$this->hour*24+$this->minute*60+$this->second+$this->millisecond/1000;
			$duration=round($time-$this->last,2);
			$this->last=$time;
			if($duration>1) $duration=1;
			$this->temp['cam'][]='
<gx:FlyTo>
	<gx:duration>'.$duration.'</gx:duration>
	<Camera>
		<longitude>'.$this->longitude.'</longitude>
		<latitude>'.$this->latitude.'</latitude>
		<altitude>'.$this->altitude.'</altitude>
		<heading>'.$this->yaw.'</heading>
		<tilt>'.($this->pitch+90).'</tilt>
		<roll>'.$this->roll.'</roll>
		<altitudeMode>absolute</altitudeMode>
	</Camera>
</gx:FlyTo>
';
		}
	}

	protected function getKMLend(){
/*		$sxe = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2">
	<Document>
		<Style id="logStyle">
			<LineStyle>
				<color>ff0000ff</color>
				<width>2</width>
				</LineStyle>
		</Style>
		<Folder>
			<Placemark>
				<name>Flight Log 06131902</name>
				<description><![CDATA[Farthest Ground Distance: 1.077km<br />Total Ground Distance: 7.812km<br />Max Altitude: 1072m<br />Max Barametric Altitude: 291.2m<br />Log Start: 2012-06-13T19:07:59Z<br />Log End: 2012-06-13T19:21:35Z<br />Total Time: 00:13:36<br /><br /><br />Generated By MatCat FeiyuLog XML Converter at http://www.letsfpv.com/feiyulog]]></description>
				<styleUrl>#logStyle</styleUrl>
				<Track>
					<altitudeMode>absolute</altitudeMode>
				</Track>
			</Placemark>
		</Folder>
	</Document>
</kml>
');*/

		return '<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2">
	<Document>
		<Style id="logStyle">
			<LineStyle>
				<color>ff0000ff</color>
				<width>2</width>
				</LineStyle>
		</Style>
		<Folder>
			<Placemark>
				<name>Flight path</name>
				<description><![CDATA[Generated by FeiyuLog Converter http://bobosch.dyndns.org/fpv]]></description>
				<styleUrl>#logStyle</styleUrl>
				<gx:Track>
					<altitudeMode>absolute</altitudeMode>
					<gx:coord>'.implode("</gx:coord>\n<gx:coord>",$this->temp['coord']).'</gx:coord>
				</gx:Track>
			</Placemark>
		</Folder>

		<gx:Tour>
			<name>Simulate flight!</name>
			<gx:Playlist>
				'.implode('',$this->temp['cam']).'
			</gx:Playlist>
		</gx:Tour>
	</Document>
</kml>
';
	}

	protected function getASSstart(){
		$this->temp=-60*60+0.01;
		return '[Script Info]
; Script generated by feiyulogconvert
; http://bobosch.dyndns.org/fpv/index.php
Title: OSD
ScriptType: v4.00+
Collisions: Normal
PlayResY: 600
PlayDepth: 0
Timer: 0,1000
Video Aspect Ratio: 0
Video Zoom: 6
Video Position: 0
 
[V4+ Styles]
Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, OutlineColour, BackColour, Bold, Italic, Underline, StrikeOut, ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, Alignment, MarginL, MarginR, MarginV, Encoding
Style: DefaultVCD, Arial,28,&H00B4FCFC,&H00B4FCFC,&H00000008,&H80000008,-1,0,0,0,100,100,0.00,0.00,1,1.00,2.00,2,30,30,30,0

[Events]
Format: Layer, Start, End, Style, Text
';
	}

	protected function getASSline(){
// 		$t=sprintf('%d:%02d:%02d.%02d',$this->hour,$this->minute,$this->second,$this->millisecond/10);
		$floor=floor($this->temp);
		$t1=date('H:i:s',$floor).sprintf('.%02d',($this->temp-$floor)*100);
		$this->temp+=$this->interval/1000;
		$floor=floor($this->temp);
		$t2=date('H:i:s',$floor).sprintf('.%02d',($this->temp-$floor)*100);
		return 'Dialogue: 0,'.$t1.','.$t2.',DefaultVCD, '.$this->yaw."\n";
	}
}
?>
