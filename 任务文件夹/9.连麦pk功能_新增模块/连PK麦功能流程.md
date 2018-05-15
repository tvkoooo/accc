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
以上错误码只用于服务器调试，返回给客户端只有  4033400020服务器问题
// 4033400020(020)服务器出现一丢丢问题

// 4033400021(021)无效的参数
// 4033400022(022)连麦PK已经结算完成，忽略错误
// 4033400023(023)连麦PK还有计时未用完
// 4033400024(024)连麦PKid号出现异常
// 4033400025(025)主播正在pk当中
// 4033400026(026)PK结束
// 4033400027(027)PK还没有开始
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
4、主播客场pk申请列表（guest_id）
(redis)zset linkcallpk:singer:guestlist:zset:(singer_id)
{
    score(uint32 timeapply),//记录主播给其他主播申请的申请时间
    member(uint64 singer_id),
} 
(cache)EXPIRE 3*24*60*60(s)//最多3天,主播关播会清除

5、主播主场pk连线列表（singer_id）
(redis)zset linkcallpk:singer:hostlist:zset:(host_id)
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
    k(string k),//
    v(uint64 v),//
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
(redis)zset linkcallpk:singer:check:pk:zset
{
    score(uint64 pkid),//当主播选择延长时间，那么新的pkid会覆盖当前旧的id号
    member(uint64 singer_id),
} 
(cache)EXPIRE 3*24*60*60(s)//默认功能关闭3天后清理

10、服务器所有正在连麦pk的主播id及金额
(redis)zset linkcallpk:pking:singer:list:zset
{
    score(uint64 gift),//该主播的礼物总值
    member(uint64 singer_id),
} 
(cache)EXPIRE 3*24*60*60(s)//最多3天,服务器主播禁用pk功能3天后消失

11、redis 服务器记录当前服务器所有 正在有弹窗确认的主播（一个主播只能有一个弹窗选择）
(redis)hash linkcallpk:singer:popup:zset:
{
    score(uint64 link_time),//连线申请时间（只取30内的数据），弹窗只有30s
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
    apply               =1；//申请连麦pk
    link                =2；//连线连麦pk
    pking               =3；//主播正在pk
    gaming              =4；//主播正在游戏
    sawing              =5；//主播正在电锯
    popup               =6；//主播收到一个连线弹窗，未处理
    no                  =7；//拒绝连线
    yes                 =8；//同意连线
    start               =9；//开始pk
    count               =10；//结算pk（这个是时间到用尽结算，暂未退出pk）
    addtime             =11；//延长pk
    over                =12；//结束pk（这个有可能是提前结算，并退出pk）
}

```
数据结构
```
message linkcallpk_pk_info
{
    required uint64 starttime		  = 1 [default = "" ]; // pk启动时间
    required uint64 pkalltime         = 2 [default = "" ]; // pk总共时间
    required uint64 host_gift		  = 3 [default =  0 ]; // 主场主播礼物值
    required uint64 guest_gift		  = 4 [default =  0 ]; // 客场主播礼物值
	required uint64 host_id	     	  = 5 [default =  0 ]; // 主场主播id
	required uint64 guest_id	      = 6 [default =  0 ]; // 客场主播id
}
enum singer_state_info
{
    apply                             =1;申请
    applying                          =2;已申请
    link                              =3;连线
    linking                           =4;已连线
}
linkcallpk_singer_info
{
    required singer_state_info singer_state       = 1 ; // 主播状态
	required uint64 singer_id		              = 2 [default =  0 ]; // 主播id
	required uint64 singer_sid		              = 3 [default =  0 ]; // 主播sid
	required string singer_icon	                  = 4 [default = "" ]; // 主播头像
	required uint32 singer_level	              = 5 [default =  0 ]; // 主播等级
	required uint32 singer_star	                  = 6 [default =  0 ]; // 主播星级
}
linkcallpk_user_info
{
    required uint64 user_gift		  = 1 [default =  0 ]; // 用户总金额
	required uint64 user_id		      = 2 [default =  0 ]; // 用户id
	required string user_icon	      = 3 [default = "" ]; // 用户头像
	required uint32 user_level	      = 4 [default =  0 ]; // 用户等级
	required uint32 user_wealth	      = 5 [default =  0 ]; // 用户星级
}
```
###D、服务端发出，客户端接收通知nt
```
1多播：推送房间pk信息
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

2单播：推送主播申请状态
message linkcallpk_singer_state_nt
{
	enum msg{ id=0x99990014;} 
	required uint64 time_now			     = 1 [default =  0 ]; // 系统时间
	required pk_state_info pk_state          = 2 [default =  0 ]; // pk状态
	required uint64 singer_id		         = 3 [default =  0 ]; // 主播id
	required string singer_icon	             = 4 [default = "" ]; // 主播头像
	required uint32 singer_level	         = 5 [default =  0 ]; // 主播等级
	required uint32 singer_star	             = 6 [default =  0 ]; // 主播星级
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
//查询的是主播作为客场，所有在线主播的列表
message linkcallpk_singer_seek_online_list_rq
{
	enum msg{ id=0x99980011;}  
	required uint64 singer_id			  = 1 [default =  0 ]; // 主播id
	required uint64 singer_sid			  = 2 [default =  0 ]; // 主播sid
}
message linkcallpk_singer_seek_online_list_rs
{
	enum msg{ id=0x99980012;}   
	required b_error.info error                      = 1                ; // error info
	required uint64 singer_id			             = 2 [default =  0 ]; // 主播id
	required uint64 time_now			             = 3 [default =  0 ]; // 系统时间
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
server->>主播:linkcallpk_singer_apply_nt
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
server->>主播:linkcallpk_singer_apply_nt
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
server->>主播:linkcallpk_singer_apply_nt
note right of client: rs返回（pk_state=yes/no）
server->>client: linkcallpk_confirm_rs
```
```
//备注：客场主播点击确认后应该双方主播进入创建pk界面，由于可能会出现的双方主播掉线，有可能某个主播收不到nt，或者主播掉线重连，因此服务器会登记一个占位pkid号，便于客户端断线重连后重构这个pk界面，pk信息只有双方主播id和sid，其他信息为0.正式pk开始后会重新生成一个新的正常pkid。
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
	required uint64 pkid				  = 7 [default =  0 ]; // pkid号（占位id）
    epeated linkcallpk_pk_info pk         = 8 ; // pk信息（占位pk信息）
}
```
###4、主场主播启动连麦pk（包括结算后再次pk，都是新产生一个pkid，全新的环境来pk）
```seq
note right of client: 主播启动连线
client->>server: linkcallpk_start_rq
note right of server:校验数据判空等状态合法性
note right of server:删除有可能存在的主播pk送礼列表
note right of server:重置（礼物金币=0）主播送礼金币
note right of server:需要单播，nt对象主播一个nt（pk_state=start）
server->>房间:linkcallpk_room_pk_info_nt(推送两个房间)
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
###5.1 主播结算pk（主场和客场主播都发结束请求，按照先到请求，并且满足pk结束时间来结算，后到的会查询已经结算，返回error忽略）
```seq
note right of client: 主播启动连线
client->>server: linkcallpk_count_rq
note right of server:校验数据判空等状态合法性
note right of server:容错，先到的满足条件开始结算，后到返回error忽略
note right of server:需要单播，nt对象主播一个nt（pk_state=count）
server->>主播:linkcallpk_singer_pklist_nt(推送pk的主客场主播)
server->>房间:linkcallpk_room_pk_info_nt(推送两个房间)
note right of client: 系统rs返回（pk_state=count）
server->>client: linkcallpk_count_rs
```
```
message linkcallpk_count_rq
{
	enum msg{ id=0x99990021;} 
    required uint64 pkid		          = 1 [default = "" ]; // pk的id号
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
###5.2 主播延长连麦pk（主播延长pk，后台结算是先结算本次pkid，把本次pkid两边礼物值和两边用户送礼信息复制一份到新的pkid当中，老的pkid信息不变，便于后续有可能的查询，并在nt和rs当中放入新的pkid及pk信息，但是原来两边主播的送礼信息还是原来那份继续操作，缓存是用singer_id做唯一索引的）
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
###5.3 主播结束连麦pk
```seq
note right of client: 主播结束连线
client->>server: linkcallpk_close_rq
note right of server:校验数据判空等状态合法性
note right of server:服务器提前进行结算pk
note right of server:需要单播，nt对象主播一个nt（pk_state=over）
server->>主播:linkcallpk_singer_apply_nt(只推送给对方连麦pk主播)
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
    required uint64 pkid		          = 3 [default = "" ]; // pk的id号
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
    required uint32 pag_num			      = 3 [default =  0 ]; // 分页号，一页10条	
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
###9、用户进入房间
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
message linkcallpk_user_comein_room_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error             = 1         ; // error info
	required uint64 singer_id		        = 2 [default =  0 ]; // 主播id
	required uint64 time_now		        = 3 [default =  0 ]; // 系统时间 	
	required uint64 pkid			        = 4 [default =  0 ]; // pkid号
    repeated linkcallpk_pk_info pk          = 5 ; // pk信息
    repeated linkcallpk_user_info h_users   = 6         ; // 主场用户送礼列表（最前的5个）
    repeated linkcallpk_user_info g_users   = 7         ; // 客场用户送礼列表（最前的5个）   
}
```
###10、用户退出房间
```
不影响该功能，忽略
```

###11、主播进入直播房间
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

###12、主播关闭直播房间
```seq
note right of mq: 主播离开触发事件
mq->>server: p_user_real_leave_channel_event
note right of server: 主播无连麦pk功能，无任何变化
note right of server: 如果主播在pk，执行 linkcallpk_close_rq
note right of server: 如果主播有申请（申请过的人）都要发nt（state=offline）
note right of server: 删除该服务器在线主播列表当中的该主播
note right of server: 删除该主播的申请列表
note right of server: 删除该主播的请求列表
server->>主播:linkcallpk_singer_apply_nt
```
