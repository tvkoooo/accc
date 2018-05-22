##连麦功能pk

###A、连麦pk错码表（code    desc）
```
0            "";//正常
// 403400011(011)网络数据库断开连接
// 403400012(012)读取数据为空
// 403400013(013)解包失败
// 403400014(014)创建pkid失败
// 403400015(015)礼物金额登记失败
// 403400016(016)mysql配置参数读取出错
以上错误码只用于服务器调试，返回给客户端只有  403400020服务器问题
// 403400020(020)服务器出现一丢丢问题

// 403400021(021)无效的参数
// 403400022(022)条件不满足，无法开启主播pk
// 403400023(023)连麦PK还有计时未用完，（备注：需要客户端处理忽略错误码，正常不会出现）
// 403400024(024)连麦pk已经结算完成，（备注：需要客户端处理忽略错误码，忽略错误）
// 403400025(025)该主播已在PK中，连线失败
// 403400026(026)PK结束
// 403400027(027)PK还没有开始（备注：需要客户端处理忽略错误码，正常不会出现）
// 403400028(028)游戏进行中，但是给客户端的:游戏进行中，无法开启主播PK
// 403400029(029)电锯进行中，但是给客户端的:游戏进行中，无法开启主播PK
// 403400030(030)主播pk弹窗未处理，但是给客户端的: 该主播已在PK中，连线失败
// 403400031(031)对方已离线，连线失败
```

###B、连麦pk数据缓存
###Redis缓存
```
0、连麦PK的配置缓存（取数据库数据，生命周期60s）
(redis)hash linkcallpk:mysql:config:info:hash:()
{
    k(uint32 info_id),//配置信息id
    v(uint32 value),  //配置信息值
} 
(cache)EXPIRE 60(s)//  生命周期60s，消失后重新到mysql取值

1 主播基础数据
linkcallpk_singer_data
{
	required uint64 singer_id		  = 1 [default =  0 ]; // 主播id
	required uint64 singer_sid		  = 2 [default =  0 ]; // 主播sid
	required string singer_icon	      = 3 [default = "" ]; // 主播头像
	required uint32 singer_level	  = 4 [default =  0 ]; // 主播等级
	required uint32 singer_star	      = 5 [default =  0 ]; // 主播星级
}
主播信息缓存
(redis)hash linkcallpk:singer:info:hash
{
    k(uint64 singer_id),//
    v(string json(linkcallpk_singer_data)),//主播信息缓存
}
(cache)EXPIRE 3*24*60*60(s)//默认功能关闭3天后清理，主播每次登陆会刷新信息和延长时间
2 送礼用户基础数据
linkcallpk_user_data
{
	required uint64 user_id		      = 1 [default =  0 ]; // 用户id
	required string user_icon	      = 2 [default = "" ]; // 用户头像
	required uint32 user_level	      = 3 [default =  0 ]; // 用户等级
	required uint32 user_wealth	      = 4 [default =  0 ]; // 用户财富登记
}
//用户数据缓存
(redis)hash linkcallpk:user:info:hash
{
    k(uint64 user_id),//
    v(string json(linkcallpk_user_data)),//用户信息缓存
}
(cache)EXPIRE 3*24*60*60(s)//默认功能关闭3天后清理，用户每次登陆会刷新信息和延长时间

3、服务器在线连麦可pk主播列表
//主播新开播，连麦pk结束，切换连麦状态会重新刷新这个时间，有利于其他主播看到来申请
(redis)zset linkcallpk:singer:onlinelist:zset()
{
    score(uint32 timeapply),//记录主播可以允许pk的登录时间
    member(uint64 singer_id),
} 
(cache)EXPIRE 3*24*60*60(s)//默认功能关闭3天后清理
4、主播客场pk申请列表（singer_id）
(redis)zset linkcallpk:singer:guestlist:zset:(singer_id)
{
    score(uint32 timeapply),//记录主播给其他主播申请的申请时间
    member(uint64 singer_id),
} 
(cache)EXPIRE 3*24*60*60(s)//最多3天,主播关播会清除

5、主播主场pk连线列表（singer_id）
(redis)zset linkcallpk:singer:hostlist:zset:(singer_id)
{
    score(uint32 timeapply),//记录其他主播给这个主播发出连麦pk申请的时间
    member(uint64 singer_id),
} 
(cache)EXPIRE 3*24*60*60(s)//最多3天,主播关播会清除

6、连麦pk号创建
(redis)string linkcallpk:pkid:create:string（）
{
    从1号开始递增
}
(cache)EXPIRE 0 永久保存
7、连麦pk信息缓存
cache linkcallpk_pk_info_cache(pkid)
{
    required uint64 starttime		  = 1 [default = "" ]; // pk启动时间
    required uint64 pkalltime		  = 2 [default = "" ]; // pk总时间  
	required uint64 host_id	     	  = 3 [default =  0 ]; // 主场主播id
	required uint64 host_sid	      = 4 [default =  0 ]; // 主场主播sid	
	required uint64 guest_id	      = 5 [default =  0 ]; // 客场主播id
	required uint64 guest_sid	      = 6 [default =  0 ]; // 客场主播sid	
}
(redis)hash linkcallpk:pk:info:hash（pkid）
{
    $pkid，json(linkcallpk_pk_info_cache(pkid))//pkid 和对应的缓存
    "pk_process",$pk_process//pk_process 和对应的这个pkid的连麦pk状态
} 
(cache)EXPIRE 3*24*60*60(s)//默认功能关闭3天后清理


8、连麦pk期间的送礼用户列表（记录连麦pk期间用户送礼价值总和）
(redis)zset linkcallpk:gift:list:zset:(singer_id)
{
    score(uint64 gift),//该用户送礼总值
    member(uint64 user_id),
} 
(cache)EXPIRE 3*24*60*60(s)//最多3天,主播pk结束会删除

9、连麦pk期间主播对应pk号
(redis)zset linkcallpk:singer:pkid:zset
{
    score(uint64 pkid),//当主播选择延长时间，那么新的pkid会覆盖当前旧的id号
    member(uint64 singer_id),
} 
(cache)EXPIRE 3*24*60*60(s)//默认功能关闭3天后清理

10、服务器所有正在连麦pk的主播视角
(redis)zset linkcallpk:singer:scene:zset
{
    score(uint64 scene),//0 ：pk视角， 1 代表：主场主播视角 2 ：代表客场主播视角 
    member(uint64 singer_id),
} 
(cache)EXPIRE 3*24*60*60(s)//最多3天,服务器主播禁用pk功能3天后消失

11、服务器所有正在连麦pk的主播id及金额
(redis)zset linkcallpk:pking:singer:gift:zset
{
    score(uint64 gift),//该主播的礼物总值
    member(uint64 singer_id),
} 
(cache)EXPIRE 3*24*60*60(s)//最多3天,服务器主播禁用pk功能3天后消失

12、redis 服务器记录当前服务器所有 正在有弹窗确认的主播（一个主播只能有一个弹窗选择）
(redis)hash linkcallpk:singer:popup:zset:
{
    score(uint64 link_time),//连线申请时间
    member(uint64 singer_id),
} 
(cache)EXPIRE 3*24*60*60(s)//最多3天,服务器主播禁用pk功能3天后消失

```
###C、客户端收到的复合数据
枚举常量主播nt和rs  连麦pk状态量
```
enum pk_state_info   
{
    offline             =0；//主播下线
    apply               =1；//申请      连麦pk
    applying            =2；//已申请    连麦pk
    link                =3；//连线      连麦pk
    linking             =4；//已连线    连麦pk    
    pking               =5；//主播正在pk
    gaming              =6；//主播正在游戏
    sawing              =7；//主播正在电锯
    popup               =8；//主播收到一个连线弹窗，未处理
    no                  =9；//拒绝连线
    yes                 =10；//同意连线
    start               =11；//开始pk
    count               =12；//结算pk（这个是时间到用尽结算，暂未退出pk）
    addtime             =13；//延长pk
    over                =14；//结束pk（这个有可能是提前结算，并退出pk）
}

```
数据结构
```
//rs回包错误信息
b_error.info
{
	required uint32 code	= 1[default = 0 ];// 0 为成功
	required string desc	= 2[default = ""];// 错误描述
}
message linkcallpk_pk_info
{
    required uint64 starttime		  = 1 [default = "" ]; // pk启动时间
    required uint64 pkalltime         = 2 [default = "" ]; // pk总共时间
    required uint64 host_gift		  = 3 [default =  0 ]; // 主场主播礼物值
    required uint64 guest_gift		  = 4 [default =  0 ]; // 客场主播礼物值
	required uint64 host_id	     	  = 5 [default =  0 ]; // 主场主播id
	required uint64 guest_id	      = 6 [default =  0 ]; // 客场主播id
}

linkcallpk_singer_info
{
    required pk_state_info pk_state               = 1 ; // 主播状态
	required uint64 singer_id		              = 2 [default =  0 ]; // 主播id
	required string singer_nick	                  = 3 [default = "" ]; // 主播昵称	
	required uint64 singer_sid		              = 4 [default =  0 ]; // 主播sid
	required string singer_icon	                  = 5 [default = "" ]; // 主播头像
	required uint32 singer_level	              = 6 [default =  0 ]; // 主播等级
	required uint32 singer_star	                  = 7 [default =  0 ]; // 主播星级
}

linkcallpk_user_info
{
    required uint64 user_gift		  = 1 [default =  0 ]; // 用户总金额
	required uint64 user_id		      = 2 [default =  0 ]; // 用户id
	required string user_nick	      = 3 [default = "" ]; // 用户昵称	
	required string user_icon	      = 3 [default = "" ]; // 用户头像
	required uint32 user_level	      = 4 [default =  0 ]; // 用户等级
	required uint32 user_wealth	      = 5 [default =  0 ]; // 用户星级
}
```
###D、服务端发出，客户端接收通知nt
```
1多播：推送房间pk场景 状态
//备注1：状态定义 0：双方pk视角   1：主场主播视角    2：客场主播视角
//备注2：客场主播点击同意pk，创建第一个视角就是pk界面视角 pk_scene =0，后续点击该请求会轮循
//备注3：开启pk 延长pk和结算pk会同步两个房间，都切回 pk界面 pk_scene=0
message linkcallpk_room_pk_scene_nt
{
	enum msg{ id=0x99990022;} 
	required uint64 singer_id			  = 1 [default =  0 ]; // 主播id
	required uint64 singer_sid			  = 2 [default =  0 ]; // 主播sid
    required string singer_nick	          = 3 [default = "" ]; // 主播昵称
	required uint64 time_now		      = 4 [default =  0 ]; // 系统时间
	required uint64 pk_scene              = 5 [default =  0 ]; // pk场景状态
	required uint64 pkid				  = 6 [default =  0 ]; // pkid号
}

2多播：推送房间pk信息
//备注如果user信息为空，说明没有送礼的变化，只是推送pk信息
//备注如果user信息只是推送最新变化的用户总金额，便于客户端小人头排序
message linkcallpk_room_pk_info_nt
{
	enum msg{ id=0x99980013;}  
 	required uint64 time_now			     = 1 [default =  0 ]; // 系统时间 
    required uint64 pkid		             = 2 [default =  0 ]; // pk的id号
    repeated linkcallpk_pk_info pk           = 3 ; // pk信息
    repeated linkcallpk_user_info user       = 4 ; // user信息
}

3单播：推送主播申请状态
message linkcallpk_singer_state_nt
{
	enum msg{ id=0x99990014;} 
	required uint64 time_now			     = 1 [default =  0 ]; // 系统时间
	required pk_state_info pk_state          = 2 [default =  0 ]; // pk状态
	required uint64 singer_id		         = 3 [default =  0 ]; // 主播id
	required string singer_icon	             = 4 [default = "" ]; // 主播头像
	required string singer_nick	             = 5 [default = "" ]; // 主播昵称
	required uint32 singer_level	         = 6 [default =  0 ]; // 主播等级
	required uint32 singer_star	             = 7 [default =  0 ]; // 主播星级
}
```

###1.1 主播打开连麦pk功能
```seq
note right of client: 主播打开连麦pk功能请求
client->>server: linkcallpk_singer_open_function_rq
note right of server:校验数据判空等状态合法性
note right of server:校验主播是否满足开播条件(星级)
note right of client: 响应主播打开连麦pk功能请求
server->>client: linkcallpk_singer_open_function_rs
```
```
//主播打开连麦pk功能开关，便于其他主播查看服务器能找到该主播
message linkcallpk_singer_open_function_rq
{
	enum msg{ id=0x99980011;}  
	required uint64 singer_id			  = 1 [default =  0 ]; // 主播id
	required uint64 singer_sid			  = 2 [default =  0 ]; // 主播sid
	required uint64 pk_open			      = 3 [default =  0 ]; // 开是1，关是0
}
message linkcallpk_singer_open_function_rs
{
	enum msg{ id=0x99980012;}   
	required b_error.info error                      = 1                ; // error info
	required uint64 singer_id			             = 2 [default =  0 ]; // 主播id
	required uint64 time_now			             = 3 [default =  0 ]; // 系统时间
	required uint64 open_state			             = 4 [default =  0 ]; // 开是1，关是0 
}
```

###1.2 主播查询当前在线满足条件主播申请列表
```seq
note right of client: 主播打开连麦pk功能请求
client->>server: linkcallpk_singer_seek_online_list_rq
note right of server:校验数据判空等状态合法性
note right of server:服务器取出当前所有在线列表当中，客户端指定需要的分页主播记录
note right of client: rs返回（主播列表list）
server->>client: linkcallpk_singer_seek_online_list_rs
```
```
//查询的是主播作为客场，所有在线主播的列表
message linkcallpk_singer_seek_online_list_rq
{
	enum msg{ id=0x99980011;}  
	required uint64 singer_id			  = 1 [default =  0 ]; // 客场主播id
	required uint64 singer_sid			  = 2 [default =  0 ]; // 客场主播sid	
	required uint32 page_num				  = 3 [default =  0 ]; // 分页号，一页10条
}
message linkcallpk_singer_seek_online_list_rs
{
	enum msg{ id=0x99980012;}   
	required b_error.info error                      = 1                ; // error info
	required uint64 singer_id			             = 2 [default =  0 ]; // 客场主播id
	required uint64 time_now			             = 3 [default =  0 ]; // 系统时间
    repeated linkcallpk_singer_info singers          = 4 ; // 主场主播列表
}

```
###2、主播客场申请pk
```seq
note right of client: 主播申请连线
client->>server: linkcallpk_apply_rq
note right of server:校验数据判空等状态合法性
note right of server:nt对象主播需要增加一条请求记录（pk_state=apply）
server->>主播:linkcallpk_singer_state_nt
note right of client: rs返回pk状态（pk_state=apply）
server->>client: linkcallpk_apply_rs
```
```
message linkcallpk_apply_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 guest_id			  = 1 [default =  0 ]; // 客场主播id
	required uint64 guest_sid			  = 2 [default =  0 ]; // 客场主播sid	
	required uint64 host_id			      = 3 [default =  0 ]; // 主场主播id
	required uint64 host_sid			  = 4 [default =  0 ]; // 主场主播sid
}
message linkcallpk_apply_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error           = 1                ; // error info
    required pk_state_info pk_state       = 2 [default =  0 ]; // pk状态
	required uint64 guest_id		      = 3 [default =  0 ]; // 客场主播id
	required uint64 host_id			      = 4 [default =  0 ]; // 主场主播id
	required uint64 time_now			  = 5 [default =  0 ]; // 系统时间	
}
```
###3.1 主播主场连线pk
```seq
note right of client: 主播连线请求
client->>server: linkcallpk_link_rq
note right of server:校验数据判空等状态合法性
note right of server:判断对象主播的状态
note right of server:（状态：pk，游戏，电锯，其他主播的连线申请，下线）
note right of server:需要单播，nt对象主播需要增加一个弹窗（pk_state=popup）
server->>主播:linkcallpk_singer_state_nt
note right of client: rs返回pk状态
note right of client:（pk_state=pking/gaming/sawing/offline/popup）
server->>client: linkcallpk_link_rs
```
```
message linkcallpk_link_rq
{
	enum msg{ id=0x99990031;} 
	required uint64 host_id			      = 1 [default =  0 ]; // 主场主播id
	required uint64 host_sid			  = 2 [default =  0 ]; // 主场主播sid
	required uint64 guest_id			  = 3 [default =  0 ]; // 客场主播id
	required uint64 guest_sid			  = 4 [default =  0 ]; // 客场主播sid
}
message linkcallpk_link_rs
{
	enum msg{ id=0x99990032;} 
	required b_error.info error           = 1                ; // error info
	required pk_state_info pk_state       = 2 [default =  0 ]; // pk状态
	required uint64 host_id			      = 3 [default =  0 ]; // 主场主播id
	required uint64 guest_id			  = 4 [default =  0 ]; // 客场主播id
	required uint64 time_now			  = 5 [default =  0 ]; // 系统时间	
}
```
###3.2 主播客场确认pk功能
```seq
note right of client: 主播连线请求
client->>server: linkcallpk_confirm_rq
note right of server:校验数据判空等状态合法性
note right of server:需要单播，nt对象主播一个nt（pk_state=yes/no）
server->>房间:linkcallpk_room_pk_scene_nt(如果是yes广播俩个房间)
server->>房间:linkcallpk_room_pk_singer_info_nt(如果是yes广播俩个房间)
server->>主播:linkcallpk_singer_state_nt
note right of client: rs返回（pk_state=yes/no）
server->>client: linkcallpk_confirm_rs
```
```
//备注：客场主播点击确认后应该双方主播进入创建pk界面，由于可能会出现的双方主播掉线，有可能某个主播收不到nt，或者主播掉线重连，因此服务器会登记一个占位pkid号，便于客户端断线重连后重构这个pk界面，pk信息只有双方主播id和sid，其他信息为0.正式pk开始后会刷新这个pkid。
enum op_code
{
    agree         = 1; //同意
    refuse        = 2; //拒绝
}
message linkcallpk_confirm_rq
{
	enum msg{ id=0x99990031;} 
	required uint64 guest_id			  = 1 [default =  0 ]; // 客场主播id
	required uint64 guest_sid			  = 2 [default =  0 ]; // 客场主播sid	
	required uint64 host_id			      = 3 [default =  0 ]; // 主场主播id
	required uint64 host_sid			  = 4 [default =  0 ]; // 主场主播sid
	required op_code code                 = 5 ; // 操作码
	
}
message linkcallpk_confirm_rs
{
	enum msg{ id=0x99990032;} 
	required b_error.info error           = 1                ; // error info
	required pk_state_info pk_state       = 2 [default =  0 ]; // pk状态
	required uint64 guest_id			  = 3 [default =  0 ]; // 客场主播id
	required uint64 host_id			      = 4 [default =  0 ]; // 主场主播id
	required uint64 time_now			  = 5 [default =  0 ]; // 系统时间	
	required op_code code                 = 6 ; // 操作码
	required uint64 pkid				  = 7 [default =  0 ]; // pkid号
    epeated linkcallpk_pk_info pk         = 8 ; // pk信息
}
pk_singer_info
{
	required uint64 singer_id		              = 2 [default =  0 ]; // 主播id
	required string singer_nick	                  = 3 [default = "" ]; // 主播昵称	
	required uint64 singer_sid		              = 4 [default =  0 ]; // 主播sid
	required string singer_icon	                  = 5 [default = "" ]; // 主播头像
	required uint32 singer_level	              = 6 [default =  0 ]; // 主播等级
	required uint32 singer_star	                  = 7 [default =  0 ]; // 主播星级
}
linkcallpk_room_pk_singer_info_nt
{
    epeated pk_singer_info h_singer               = 1 ; // 主场主播信息
    epeated pk_singer_info g_singer               = 2 ; // 客场主播信息
}
```
###4.1、主场主播启动连麦pk（如果是第一次启动，会是原来创建的pkid，如果是再次启动，会生成一个新的pkid）
```seq
note right of client: 主播启动连线
client->>server: linkcallpk_start_rq
note right of server:校验数据判空等状态合法性
note right of server:删除有可能存在的主播pk送礼列表
note right of server:重置（礼物金币=0）主播送礼金币
note right of server:需要单播，nt对象主播一个nt（pk_state=start）
server->>房间:linkcallpk_room_pk_info_nt(推送两个房间)
server->>房间:linkcallpk_room_pk_scene_nt(推送两个房间)
note right of client: 系统rs返回（pk_state=start）
server->>client: linkcallpk_start_rs
```
```
message linkcallpk_start_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 host_id			      = 1 [default =  0 ]; // 主场主播id
	required uint64 host_sid			  = 2 [default =  0 ]; // 主场主播sid
	required uint64 guest_id			  = 3 [default =  0 ]; // 客场主播id
	required uint64 guest_sid			  = 4 [default =  0 ]; // 客场主播sid
}
message linkcallpk_start_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error           = 1                ; // error info
	required uint64 host_id				  = 2 [default =  0 ]; // 主场主播id
	required uint64 time_now		      = 3 [default =  0 ]; // 系统时间
	required pk_state_info pk_state       = 4 [default =  0 ]; // pk状态
	required uint64 pkid				  = 5 [default =  0 ]; // pkid号
    epeated linkcallpk_pk_info pk         = 6 ; // pk信息

}

```
###4.2、主场切换场景视角（pk_scene pk场景 顺序：pk界面0--主场1--客场2 ）
```seq
note right of client: 主播启动连线
client->>server: linkcallpk_pking_switch_scene_rq
note right of server:校验数据判空等状态合法性
note right of server:取出服务器上次状态，顺序走下个状态（客场主播同意pk时，pk_scene=0）
note right of server:（备注：开启pk和结算pk会同步两个房间都切回 pk界面 pk_scene=0）
server->>房间:linkcallpk_room_pk_scene_nt(只广播这个主播房间)
note right of client: 系统rs返回（pk_scene状态吗）
server->>client: linkcallpk_pking_switch_scene_rq
```
```
message linkcallpk_pking_switch_scene_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 singer_id			      = 1 [default =  0 ]; // 主播id
	required uint64 singer_sid			      = 2 [default =  0 ]; // 主播sid
}
message linkcallpk_pking_switch_scene_rq
{
	enum msg{ id=0x99990022;} 
	required b_error.info error           = 1                ; // error info
	required uint64 singer_id			  = 2 [default =  0 ]; // 主播id
	required uint64 time_now		      = 3 [default =  0 ]; // 系统时间
	required uint64 pk_scene              = 4 [default =  0 ]; // pk场景
	required uint64 pkid				  = 5 [default =  0 ]; // pkid号
}
```
###5.1 主播结算pk（主场和客场主播都可以发结算信息，如果不发结算，系统会自动结算）
```seq
note right of client: 主播启动连线
client->>server: linkcallpk_count_rq
note right of server:校验数据判空等状态合法性
note right of server:容错，先到的满足条件开始结算，后到返回error忽略
note right of server:需要单播，nt对象主播一个nt（pk_state=count）
server->>主播:linkcallpk_singer_pklist_nt(推送pk的主客场主播)
server->>房间:linkcallpk_room_pk_info_nt(推送两个房间)
server->>房间:linkcallpk_room_pk_scene_nt(推送两个房间)
note right of client: 系统rs返回（pk_state=count）
server->>client: linkcallpk_count_rs
```
```
message linkcallpk_count_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 guest_id			  = 2 [default =  0 ]; // 客场主播id
	required uint64 guest_sid			  = 3 [default =  0 ]; // 客场主播sid	
	required uint64 host_id			      = 4 [default =  0 ]; // 主场主播id
	required uint64 host_sid			  = 5 [default =  0 ]; // 主场主播sid   
}
message linkcallpk_count_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error           = 1                ; // error info
	required uint64 time_now		      = 2 [default =  0 ]; // 系统时间 
	required pk_state_info pk_state       = 3 [default =  0 ]; // pk状态
	required uint64 pkid		          = 4 [default = "" ]; // pk的id号
    epeated linkcallpk_pk_info pk         = 5 ; // pk信息
}
```
###5.2 主播延长连麦pk（主播延长pk，重新生成一个pkid，沿用上个pkid数据继续pk）
```seq
note right of client: 主播启动连线
client->>server: linkcallpk_addtime_rq
note right of server:校验数据判空等状态合法性
note right of server:服务器继续沿用上次数据，新开一个pkid号
note right of server:需要单播，nt对象主播一个nt（pk_state=addtime）
server->>主播:linkcallpk_singer_pklist_nt(推送pk的客场主播)
server->>房间:linkcallpk_room_pk_info_nt(推送两个房间)
note right of client: 系统rs返回（pk_state=addtime）
server->>client: linkcallpk_addtime_rs
```
```
message linkcallpk_addtime_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 host_id			      = 1 [default =  0 ]; // 主场主播id
	required uint64 host_sid			  = 2 [default =  0 ]; // 主场主播sid 
	required uint64 guest_id			  = 3 [default =  0 ]; // 客场主播id
	required uint64 guest_sid			  = 4 [default =  0 ]; // 客场主播sid	

}
message linkcallpk_addtime_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error           = 1                ; // error info
	required uint64 host_id				  = 2 [default =  0 ]; // 主场主播id
	required uint64 time_now		      = 3 [default =  0 ]; // 系统时间
	required pk_state_info pk_state       = 4 [default =  0 ]; // pk状态
	required uint64 pkid				  = 5 [default =  0 ]; // 新的pkid号
    epeated linkcallpk_pk_info pk         = 6 ; // pk信息
}
```
###5.3 主播结束连麦pk（释放PK情景，一个主播只能存在一个pk情景，从创建pk界面开始）
```seq
note right of client: 主播结束连线
client->>server: linkcallpk_close_rq
note right of server:校验数据判空等状态合法性
note right of server:服务器提前进行结算pk
note right of server:需要单播，nt对象主播一个nt（pk_state=over）
server->>主播:linkcallpk_singer_state_nt(只推送给对方连麦pk主播)
server->>房间:linkcallpk_room_pk_info_nt(推送两个房间)
note right of server:刷新服务器主播在线pk列表时间
note right of client: 系统rs返回（pk_state=over）
server->>client: linkcallpk_close_rs
```
```
//如果主播未开启pk功能，直接选择结束，pkid=0，rs返回data都是0
message linkcallpk_close_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 guest_id			  = 2 [default =  0 ]; // 客场主播id
	required uint64 guest_sid			  = 3 [default =  0 ]; // 客场主播sid	
	required uint64 host_id			      = 4 [default =  0 ]; // 主场主播id
	required uint64 host_sid			  = 5 [default =  0 ]; // 主场主播sid 
}
message linkcallpk_close_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error           = 1                ; // error info
	required uint64 host_id				  = 2 [default =  0 ]; // 主场主播id
	required uint64 time_now		      = 3 [default =  0 ]; // 系统时间
	required pk_state_info pk_state       = 4 [default =  0 ]; // pk状态
	required uint64 pkid				  = 5 [default =  0 ]; // pkid号
    epeated linkcallpk_pk_info pk         = 6 ; // pk信息	
}
```
###6、用户查询连麦pk主播信息
```seq
note right of client: 用户查询连麦pk主播信息
client->>server: linkcallpk_user_seek_pk_rq
note right of server:校验数据判空等状态合法性
note right of client: 响应用户查询连麦pk主播信息
server->>client: linkcallpk_user_seek_pk_rs
```
```
message linkcallpk_user_seek_pk_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 singer_id			  = 1 [default =  0 ]; // 主播id
	required uint64 singer_sid			  = 2 [default =  0 ]; // 主播sid 
}
message linkcallpk_user_seek_pk_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error       = 1                ; // error info
	required uint64 singer_id		  = 2 [default =  0 ]; // 主播id
	required uint64 time_now		  = 3 [default =  0 ]; // 系统时间 
	required uint64 pkid			  = 4 [default =  0 ]; // pkid号
    repeated linkcallpk_pk_info pk    = 5 ; // pk信息
}
```
###7、 用户查询连麦pk用户送礼信息(只用于推送最前面的5个列表，用于客户展示最前面的5个人头)
```seq
note right of client: 用户查询户送礼信息
client->>server: linkcallpk_user_seek_gift_rq
note right of server:校验数据判空等状态合法性
note right of client: 响应用户查询户送礼信息
server->>client: linkcallpk_user_seek_gift_rs
```
```
message linkcallpk_user_seek_gift_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 singer_id			  = 1 [default =  0 ]; // 主播id
	required uint64 singer_sid			  = 2 [default =  0 ]; // 主播sid 
}
message linkcallpk_user_seek_gift_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error         = 1         ; // error info
	required uint64 singer_id		    = 2 [default =  0 ]; // 查询的主播号
	required uint64 time_now		    = 3 [default =  0 ]; // 系统时间   
    required uint64 pkid		        = 4 [default = "" ]; // pk的id号	
    repeated linkcallpk_user_info users = 5         ; // 用户送礼列表（最前的5个）
}
```

###8、主播查询客场其他主播给自己的连麦pk列表申请的请求列表
```seq
note right of client: 主播按页查询可当前连线请求列表
client->>server: linkcallpk_singer_seek_link_list_rq
note right of server:校验数据判空等状态合法性
note right of client: 响应主播按页查询可当前连线请求列表
server->>client: linkcallpk_singer_seek_link_list_rs
```
```
message linkcallpk_singer_seek_link_list_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 singer_id			  = 1 [default =  0 ]; // 主播id
	required uint64 singer_sid			  = 2 [default =  0 ]; // 主播房间号
    required uint32 page_num			      = 3 [default =  0 ]; // 分页号，一页10条	
}
message linkcallpk_singer_seek_link_list_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error             = 1         ; // error info
	required uint64 singer_id		        = 2 [default =  0 ]; // 主播id
	required uint64 time_now		        = 3 [default =  0 ]; // 系统时间 	
	repeated linkcallpk_singer_info singers = 4         ; // 主播列表信息
}
```
###9、用户进入房间（这个是总命令，可以返回连麦pk信息）
```seq
note right of client: 用户进入房间
client->>server: linkcallpk_user_comein_room_rq
note right of server:校验数据判空等状态合法性
note right of client: 如果该直播间没有连麦PK，pkid=0
note right of client: 如果该直播间刚刚创建PK，pk["starttime"]=0
note right of client: 如果该直播间正在PK，有可能有礼物列表
note right of client: 响应用户进入房间
server->>client: linkcallpk_user_comein_room_rs
```
```
message linkcallpk_user_comein_room_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 singer_id			  = 1 [default =  0 ]; // 主播id
	required uint64 singer_sid			  = 2 [default =  0 ]; // 主播房间号
}
pk_singer_info
{
	required uint64 singer_id		              = 2 [default =  0 ]; // 主播id
	required string singer_nick	                  = 3 [default = "" ]; // 主播昵称	
	required uint64 singer_sid		              = 4 [default =  0 ]; // 主播sid
	required string singer_icon	                  = 5 [default = "" ]; // 主播头像
	required uint32 singer_level	              = 6 [default =  0 ]; // 主播等级
	required uint32 singer_star	                  = 7 [default =  0 ]; // 主播星级
}
message linkcallpk_user_comein_room_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error             = 1         ; // error info
	required uint64 singer_id		        = 2 [default =  0 ]; // 主播id
	required uint64 time_now		        = 3 [default =  0 ]; // 系统时间 	
	required uint64 pkid			        = 4 [default =  0 ]; // pkid号
    repeated linkcallpk_pk_info pk          = 5 ; // pk信息
    epeated pk_singer_info h_singer         = 6 ; // 主场主播信息
    epeated pk_singer_info g_singer         = 7 ; // 客场主播信息
    repeated linkcallpk_user_info h_users   = 8 ; // 主场用户送礼列表（最前的5个）
    repeated linkcallpk_user_info g_users   = 9 ; // 客场用户送礼列表（最前的5个）   
}
```
###10、用户退出房间
```
不影响该功能，忽略
```

###11、主播进入直播房间（可以查看是否主播是否开启pk功能，是否有pk弹窗显示，是否启动了pk界面）
```seq
note right of client: 主播进入直播房间
client->>server: linkcallpk_singer_comein_room_rq
note right of server:校验数据判空等状态合法性
note right of client: 首先确认自己是否已经退出pk直播
note right of client: 其次确认是否有请求弹窗未处理
note right of client: 查看是否自己处于pk当中（pkid号）
note right of client: 响应主播进入直播房间
server->>client: linkcallpk_singer_comein_room_rs
```
```
message linkcallpk_singer_comein_room_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 singer_id			  = 1 [default =  0 ]; // 主播id
	required uint64 singer_sid			  = 2 [default =  0 ]; // 主播房间号
}
message linkcallpk_singer_comein_room_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error             = 1         ; // error info
	required uint64 time_now		        = 2 [default =  0 ]; // 系统时间
	required uint32 functiontime		    = 3 [default =  0 ]; // 主播开启pk功能时间
	required uint64 singer_id		        = 4 [default =  0 ]; // 主播自己id
	required uint64 popup_time		        = 5 [default =  0 ]; // 发弹窗的时间
	required uint64 popup_id		        = 6 [default =  0 ]; // 发弹窗的主播id
	required uint64 popup_live		        = 7 [default =  0 ]; // 弹窗的生命时间	
	required uint64 pkid			        = 8 [default =  0 ]; // pkid号
    repeated linkcallpk_pk_info pk          = 9 ; // pk信息
}
```
```
备注：如果主播在已经不在pk，忽略，安装新直播进场处理
备注：1 如果主播还开着pk功能，需要查看是否还有弹窗，有，需要操作弹窗
      2 如果主播还开着pk功能，需要查看当前是否刚刚创建pk场景，有，需要展示
      3 如果主播还开着pk功能，需要查看当前是否正在pk，有，需要下拉当前双方送礼列表（5个
```

###12、主播关闭直播房间（这个是由服务器发送主播离场事件触发）
```seq
note right of mq: 主播离开触发事件
mq->>server: p_user_real_leave_channel_event
note right of server: 主播无连麦pk功能，无任何变化
note right of server: 如果主播在pk，执行 linkcallpk_close_rq
note right of server: 如果主播有申请（申请过的人）都要发nt（state=offline）
note right of server: 删除该服务器在线主播列表当中的该主播
note right of server: 删除该主播的申请列表
note right of server: 删除该主播的请求列表
server->>主播:linkcallpk_singer_state_nt
```
