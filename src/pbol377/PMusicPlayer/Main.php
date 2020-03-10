<?php
namespace pbol377\PMusicPlayer;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\tile\Tile;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\scheduler\Task;

class Main extends PluginBase implements Listener {
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents ($this, $this);
         $this->data = new Config($this->getDataFolder() . "data.yml", Config::YAML);
         $this->db = $this->data->getAll();
         $this->count = new Config($this->getDataFolder() . "count.yml", Config::YAML);
         $this->ct = $this->count->getAll();
         $this->count2 = new Config($this->getDataFolder() . "count2.yml", Config::YAML);
         $this->ct2 = $this->count2->getAll();
         if(!isset($this->db))$this->db=[];
         $this->ct[0]=0;
         $this->ct[1]=0;
         $this->ct2[1]=0;
         $ttask = new TTask($this);
         $this->getScheduler()-> scheduleRepeatingTask($ttask,20);
		}
	
	public function sendMsg($sender){
		$pf="§e$$ §§a음악  §e$$";
		$sender->sendMessage($pf."/음악 재생 < 음악 이름 > :: 해당 음악을 5분 간격으로 재생합니다.");
			$sender->sendMessage($pf."/음악 일시정지");
			$sender->sendMessage($pf."/음악 목록연속재생 :: 목록에 있는 음악을 5분간격으로 연속재생합니다.");
			$sender->sendMessage($pf."/음악 목록추가 < 음악 이름 > :: 리소스파일에 있는 해당 음악을 플레이 목록에 넣습니다.");
			//$sender->sendMessage($pf."/음악 목록삭제 < 음악 이름 > :: 플레이 목록에 있는 해당 음악을 플레이 목록에서 삭제합니다.");
			$sender->sendMessage($pf."/음악 이전음악");
			$sender->sendMessage($pf."/음악 다음음악");
		}
	
	public function onCheck($a){
		if($a == "13" || $a == "11" || $a == "cat" || $a == "wait" || $a == "ward" || $a == "stand" || $a == "stall" || $a == "mellohi" || $a == "mall" || $a == "far" || $a == "charp" || $a == "blocks"){
			return false;
			}
		else{
			return true;
			}
		}
	
	public function onCommand(Commandsender $sender, Command $command, string $label, array $args) : bool{
	$name = $sender->getName();
	$cmd = $command->getName();
	$pf="§e[$ §§a음악  §e$]§7 ";
	/*if (!$sender instanceof Player) {
		$sender->sendMessage("§c§lProhibited in Console");
		return true;
		}*/
	if($cmd == "음악"){
		if(!isset($args[0])){
			$sender->sendMessage($pf."/음악 재생 < 음악 이름 > :: 해당 음악을 재생합니다.");
			$sender->sendMessage($pf."/음악 일시정지");
			$sender->sendMessage($pf."/음악 재생목록");
			$sender->sendMessage($pf."/음악 목록연속재생 :: 목록에 있는 음악을 5분간격으로 연속재생합니다. 다시치면 중지됩니다.");
			$sender->sendMessage($pf."/음악 목록추가 < 음악 이름 > :: 리소스파일에 있는 해당 음악을 플레이 목록에 넣습니다.");
			$sender->sendMessage($pf."/음악 목록삭제 < 음악 이름 > :: 플레이 목록에 있는 해당 음악을 플레이 목록에서 삭제합니다.");
			$sender->sendMessage($pf."/음악 이전음악");
			$sender->sendMessage($pf."/음악 다음음악");
			return true;
			}
		if($args[0]=="재생"){
			if($this->onCheck($args[1])){
				$sender->sendMessage($pf."음악 이름은 11, 13, wait, cat, ward, stand, stall, mellohi, mall, far, charp, blocks만 가능합니다. 만약 음악의 이름이 위와 같지 않다면 리소스팩에서 수정해주시기 바랍니다.");
				return true;
				}
			if(count($args)!=2){
				$this->sendMsg($sender);
				return true;
				}
			if($this->ct[1]==1){
				$sender->sendMessage($pf."현재 재생목록이 연속재생되고 있습니다. /음악 목록연속재생을 쳐 취소 후 이용해주세요.");
				return true;
				}
			$sender->sendMessage($pf."만약 리소스 파일에 {$args[1]}음악이 없을 경우 오류가 날 수 있습니다. 재생이 시작됩니다.");
			$this->startSound($args[1]);
			return true;
			}//args
		else if($args[0]=="일시정지"){
			if(count($args)!=2){
				$this->sendMsg($sender);
				return true;
				}
			$sender->sendMessage($pf."만약 리소스 파일에 {$args[1]}음악이 없을 경우 오류가 날 수 있습니다. 재생이 중지됩니다.");
			$this->stopSound($args[1]);
			return true;
			}
		else if($args[0]=="목록추가"){
			if(count($args)!=2){
				$this->sendMsg($sender);
				return true;
				}
			if($this->onCheck($args[1])){
				$sender->sendMessage($pf."음악 이름은 11, 13, wait, cat, ward, stand, stall, mellohi, mall, far, charp, blocks만 가능합니다. 만약 음악의 이름이 위와 같지 않다면 리소스팩에서 수정해주시기 바랍니다.");
				return true;
				}
			$array=array("name"=>$args[1]);
			array_push($this->db, $array);
			$this->save();
			$sender->sendMessage($pf."만약 리소스 파일에 {$args[1]}음악이 없을 경우 오류가 날 수 있습니다. 목록에 추가되었습니다.");
			return true;
			}
		else if($args[0]=="재생목록"){
			foreach($this->db as $key => $val){
				$sender->sendMessage($pf."[ {$key} ] || [ {$val["name"]}");
				}
			return true;
			}
		/*else if($args[0]=="목록삭제"){
			if(count($args)!=2){
				$this->sendMsg($sender);
				return true;
				}
			if(!isset($this->db[$args[1]])){
				$sender->sendMessage($pf."존재하지 않는 음악입니다.");
				return true;
				}
			unset($this->db[$args[1]]);
			foreach($this->db as $key => $val){
				if($key>$args[1]){
					$this->db[$key-1] = $this->db[$key];
					}
				}
			$this->save();
			return true;
			}*/
		else if($args[0]=="목록연속재생"){
			if($this->ct[1]==0){
				$sender->sendMessage($pf."목록 재생이 시작되었습니다.");
				$this->ct[1]=1;
				$this->ct2[1]=300;
				return true;
				}
			else if($this->ct[1]==1){
				$sender->sendMessage($pf."목록 재생이 중지되었습니다.");
				$this->ct[1]=0;
				return true;
				}
			}
		else if($args[0]=="이전음악"){
			if(!isset($this->db[$this->ct[0]-1])){
				$sender->sendMessage($pf."이전 음악이 존재하지 않습니다");
				return true;
				}
			$this->stopSound($this->db[$this->ct[0]]["name"]);
			$this->ct[0]-=1;
			$this->startSound($this->db[$this->ct[0]]["name"]);
			return true;
			}
		else if($args[0]=="다음음악"){
			if(!isset($this->db[$this->ct[0]+1])){
				$sender->sendMessage($pf."다음 음악이 존재하지 않습니다");
				return true;
				}
			$this->stopSound($this->db[$this->ct[0]]["name"]);
			$this->ct[0]+=1;
			$this->startSound($this->db[$this->ct[0]]["name"]);
			return true;
			}
		}//cmd
		return true;
	}//명
	
	public function startSound($sound){
		foreach ( $this->getServer()->getOnlinePlayers() as $players ){
			$packet = new PlaySoundPacket();
			$packet->soundName = "record.".$sound;
			$packet->x = $players->getPlayer()->getX();
			$packet->y = $players->getPlayer()->getY();
			$packet->z = $players->getPlayer()->getZ();
			$packet->volume = 99;
			$packet->pitch = 1;
			$players->getPlayer()->dataPacket($packet);
		}
	}
	
	public function stopSound($sound){
		foreach ( $this->getServer()->getOnlinePlayers() as $players ){
		$packet = new StopSoundPacket();
		$packet->soundName = "record.".$sound;
		$packet->stopAll = $sound;
		$players->getPlayer()->dataPacket($packet);
		}
		}
	
	public function getList(){
		if(isset($this->db[0])){
			return $this->db[$this->ct[0]]["name"];
			}
		}
	
	public function getMode(){
		return $this->ct[1];
		}
	
	public function resetCount(){
		if(!isset($this->db[$this->ct[0]+1])){
			$this->ct[0]=0;
			}
		}
	
	public function save(){
		$this->data->setAll($this->db);
		$this->data->save();
		}
	
	public function Count(){
		return $this->ct2[1];
		}
		
	public function up(){
		$this->ct2[1]+=1;
		}
		
	public function oo(){
		$this->ct2[1]=0;
		}
	
	}//끝
	
class TTask extends Task{
	private $owner;
	public function __construct(Main $owner){
				$this->owner = $owner;
			}
	public function onRun( $currentTick ) {
		$m = $this->owner->getMode();
		$a=$this->owner->Count();
		if($a==300){
		if($m==1){
		$this->owner->resetCount();
		$list = $this->owner->getList();
		$this->owner->startSound($list);
		$this->owner->oo();
		}
		}
		$this->owner->up();
		}
	}
