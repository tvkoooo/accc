##连麦功能PK

###A、连麦PK错码表（code    desc）
```
0            "";//正常

```

###B、连麦PK数据缓存
###Redis缓存
```
1 主播基础数据
linkcallPK_singer_data
{
	required uint64 singer_sid		  = 1 [default =  0 ]; // 房间号
	required uint64 singer_id		  = 2 [default =  0 ]; // 主播id
	required string singer_icon	      = 3 [default = "" ]; // 主播头像
	required uint32 singer_level	  = 1 [default =  0 ]; // 主播等级
	required uint32 singer_star	      = 1 [default =  0 ]; // 主播星级
}
主播信息缓存
(redis)hash linkcallPK:singer:info:hash
{
    k(uint64 singer_id),//
    v(string json(linkcallPK_singer_data)),//主播信息缓存
}
(cache)EXPIRE 3*24*60*60(s)//默认功能关闭3天后清理，主播每次登陆会刷新信息和延长时间
2 送礼用户基础数据
linkcallPK_user_data
{
	required uint64 user_id		      = 1 [default =  0 ]; // 用户id
	required string user_icon	      = 2 [default = "" ]; // 用户头像
	required uint32 user_level	      = 3 [default =  0 ]; // 用户等级
	required uint32 user_wealth	      = 4 [default =  0 ]; // 用户星级
}
//用户数据缓存
(redis)hash linkcallPK:singer:info:hash
{
    k(uint64 user_id),//
    v(string json(linkcallPK_user_data)),//用户信息缓存
}
(cache)EXPIRE 3*24*60*60(s)//默认功能关闭3天后清理，用户每次登陆会刷新信息和延长时间

3、服务器在线连麦可PK主播列表
enum singer_code
{ 
    code_default  = 0; //异常
    code_apply    = 1; //主播新上线，可以允许连麦PK
    code_link     = 2; //主播连线申请
    code_offline  = 3; //主播连线下线
    code_PK       = 4; //主播正在PK
}
(redis)hash linkcallPK:singer:list:hash
{
    k(uint64 singer_id),//
    v(uint32 singer_code),//主播PK状态信息
} 
(cache)EXPIRE 3*24*60*60(s)//默认功能关闭3天后清理

4、主播客场连麦PK列表（singer_id）
(redis)zset linkcallPK:singer:guestlist:zset:(singer_id)
{
    score(uint32 timeapply),//记录其他主播给这个主播发出连麦PK申请的时间
    member(uint64 singer_id),
} 
(cache)EXPIRE 3*24*60*60(s)//最多3天,主播关播会清除


5、连麦PK期间的送礼用户列表（记录连麦PK期间用户送礼价值总和）
(redis)zset linkcallPK:pkid:gift:list:zset:(singer_id)
{
    score(uint64 gift),//该用户送礼总值
    member(uint64 user_id),
} 
(cache)EXPIRE 3*24*60*60(s)//最多3天,主播PK结束会删除

6、服务器所有正在连麦PK的主播房间
(redis)zset linkcallPK:PK:singer:list:zset
{
    score(uint64 gift),//该主播的礼物总值
    member(uint64 singer_di),
} 
(cache)EXPIRE 3*24*60*60(s)//最多3天,服务器主播禁用pk功能3天后消失

```
###C、客户端收到的复合数据
```
enum singer_code
{ 
    code_default  = 0; //异常
    code_apply    = 1; //主播新上线，可以允许连麦PK
    code_link     = 2; //主播连线申请
    code_offline  = 3; //主播连线下线
    code_PK       = 4; //主播正在PK
}
message linkcallPK_pkinfo
{
    required uint64 starttime		  = 1 [default = "" ]; // pk启动时间
    required uint64 PKalltime         = 2 [default = "" ]; // pk总共时间
    required uint64 host_gift		  = 3 [default =  0 ]; // 主场主播礼物值
    required uint64 guest_gift		  = 4 [default =  0 ]; // 客场主播礼物值
	required uint64 host_id	     	  = 5 [default =  0 ]; // 主场主播id
	required uint64 guest_id	      = 6 [default =  0 ]; // 客场主播id
}
linkcallPK_singer_info
{
    repeated singer_code code         = 1 ; // 主播PK信息
	required uint64 singer_sid		  = 2 [default =  0 ]; // 房间号
	required uint64 singer_id		  = 3 [default =  0 ]; // 主播id
	required string singer_icon	      = 4 [default = "" ]; // 主播头像
	required uint32 singer_level	  = 5 [default =  0 ]; // 主播等级
	required uint32 singer_star	      = 6 [default =  0 ]; // 主播星级
}
linkcallPK_user_gift
{
    required uint64 user_gift		  = 1 [default =  0 ]; // 用户总金额
	required uint64 user_id		      = 2 [default =  0 ]; // 用户id
	required string user_icon	      = 3 [default = "" ]; // 用户头像
	required uint32 user_level	      = 4 [default =  0 ]; // 用户等级
	required uint32 user_wealth	      = 5 [default =  0 ]; // 用户星级
}
```
###D、服务端发出，客户端接收通知
```
1多播：推送房间PK信息（是否有需要）
message linkcallPK_room_pkinfo_nt
{
	enum msg{ id=0x99980013;}  
    repeated linkcallPK_pkinfo data          = 1 ; // PK信息
 	required uint64 time_now			     = 2 [default =  0 ]; // 系统时间   
}

2单播：推送主播PK列表

message linkcallPK_singer_PKlist_nt
{
	enum msg{ id=0x99990014;} 
	required uint64 time_now			     = 1 [default =  0 ]; // 系统时间
    repeated linkcallPK_singer_info data     = 2 ; // 主播信息
}
```
###1、主播打开连麦PK功能
```seq
note right of client: 主播打开连麦PK功能请求
client->>server: linkcallPK_open_rq
note right of server:校验数据判空等状态合法性
note right of server:校验主播是否在（在线连麦可PK列表）
note right of client: 响应主播打开连麦PK功能请求
server->>client: linkcallPK_open_rs
```
```
message linkcallPK_open_rq
{
	enum msg{ id=0x99980011;}  
	required uint64 host_sid			  = 1 [default =  0 ]; // 主场房间号
	required uint64 host_id				  = 2 [default =  0 ]; // 主场主播id
}
message linkcallPK_open_rs
{
	enum msg{ id=0x99980012;}   
	required b_error.info error               = 1                ; // error info
	required uint64 host_sid				  = 2 [default =  0 ]; // 主场房间号
	required uint64 host_id				      = 3 [default =  0 ]; // 主场主播id
	required uint64 time_now			      = 4 [default =  0 ]; // 系统时间
    repeated linkcallPK_singer_info datas     = 5 ; // 主场主播列表
}

```
###2、主播主场申请连麦PK功能
```seq
note right of client: 主播申请连线
client->>server: linkcallPK_apply_rq
note right of server:校验数据判空等状态合法性
server->>主播:linkcallPK_singer_apply_nt
note right of client: 响应主播申请连线
server->>client: linkcallPK_apply__rs
```
```
message linkcallPK_apply_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 host_sid			  = 1 [default =  0 ]; // 主场房间号
	required uint64 host_id				  = 2 [default =  0 ]; // 主场主播id
	required uint64 guest_id			  = 3 [default =  0 ]; // 客场主播id
}
message linkcall_apply_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error           = 1                ; // error info
	required uint32 singer_code           = 2 [default =  0 ]; // 申请状态
	required uint64 host_sid			  = 3 [default =  0 ]; // 主场房间号
	required uint64 host_id				  = 4 [default =  0 ]; // 主场主播id
	required uint64 guest_id			  = 5 [default =  0 ]; // 客场主播id
	required uint64 time_now			  = 6 [default =  0 ]; // 系统时间	
}
```
###3、主播客场同意连线连麦PK功能
```seq
note right of client: 主播连线请求
client->>server: linkcallPK_link_rq
note right of server:校验数据判空等状态合法性
server->>主播:linkcallPK_singer_apply_nt
note right of client: 响应主播连线请求
server->>client: linkcallPK_link__rs
```
```
message linkcallPK_link_rq
{
	enum msg{ id=0x99990031;} 
	required uint64 guest_sid			  = 1 [default =  0 ]; // 客场房间号
	required uint64 guest_id			  = 2 [default =  0 ]; // 客场主播id
	required uint64 host_id			      = 3 [default =  0 ]; // 主场主播id
}
message linkcallPK_link_rs
{
	enum msg{ id=0x99990032;} 
	required b_error.info error           = 1                ; // error info
	required uint32 singer_code           = 2 [default =  0 ]; // 申请状态
	required uint64 guest_sid			  = 3 [default =  0 ]; // 客场房间号
	required uint64 guest_id			  = 4 [default =  0 ]; // 客场主播id
	required uint64 host_id			      = 5 [default =  0 ]; // 主场主播id
	required uint64 time_now			  = 6 [default =  0 ]; // 系统时间	
}
```
###4、主播启动连麦PK功能
```seq
note right of client: 主播启动连线
client->>server: linkcallPK_start_rq
note right of server:校验数据判空等状态合法性
server->>主播:linkcallPK_singer_PKlist_nt(推送PK的主播)
server->>房间:linkcallPK_room_pkinfo_nt(推送两个房间)
note right of client: 响应主播启动连线
server->>client: linkcallPK_start_rs
```
```
message linkcallPK_start_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 host_sid			  = 1 [default =  0 ]; // 主场房间号
	required uint64 host_id				  = 2 [default =  0 ]; // 主场主播id
	required uint64 guest_id			  = 3 [default =  0 ]; // 客场主播id
}
message linkcallPK_start_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error           = 1                ; // error info
	required uint64 host_sid			  = 2 [default =  0 ]; // 主场房间号
	required uint64 host_id				  = 3 [default =  0 ]; // 主场主播id
	required uint64 time_now		      = 4 [default =  0 ]; // 系统时间
	required uint64 singer_code		      = 5 [default =  0 ]; // 启动连麦PK状态

}
```
###5、主播延长连麦PK功能
```seq
note right of client: 主播启动连线
client->>server: linkcallPK_addtime_rq
note right of server:校验数据判空等状态合法性
server->>主播:linkcallPK_singer_PKlist_nt(推送PK的主播)
server->>房间:linkcallPK_room_pkinfo_nt(推送两个房间)
note right of client: 响应主播启动连线
server->>client: linkcallPK_addtime_rs
```
```
message linkcallPK_start_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 host_sid			  = 1 [default =  0 ]; // 主场房间号
	required uint64 host_id				  = 2 [default =  0 ]; // 主场主播id
	required uint64 guest_id			  = 3 [default =  0 ]; // 客场主播id
	required uint64 addtime  			  = 4 [default =  0 ]; // 延长时间
}
message linkcallPK_start_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error           = 1                ; // error info
	required uint64 host_id				  = 2 [default =  0 ]; // 主场主播id
	required uint64 guest_id			  = 3 [default =  0 ]; // 客场主播id	
	required uint64 time_now		      = 4 [default =  0 ]; // 系统时间   
    epeated linkcallPK_pkinfo data        = 5 ; // Pk信息
}
```
###5、主播结束连麦PK功能
```seq
note right of client: 主播结束连线
client->>server: linkcallPK_close_rq
note right of server:校验数据判空等状态合法性
server->>主播:linkcallPK_singer_apply_nt(是否所有有关联的都要推送主客场)
server->>房间:linkcallPK_room_pkinfo_nt(推送两个房间)
note right of client: 响应主播启动连线
server->>client: linkcallPK_close_rs
```
```
message linkcallPK_close_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 host_sid			  = 1 [default =  0 ]; // 主场房间号
	required uint64 host_id				  = 2 [default =  0 ]; // 主场主播id
	required uint64 guest_id			  = 3 [default =  0 ]; // 客场主播id
}
message linkcallPK_close_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error           = 1                ; // error info
	required uint64 host_id				  = 2 [default =  0 ]; // 主场主播id
	required uint64 guest_id			  = 3 [default =  0 ]; // 客场主播id
	required uint64 time_now		      = 4 [default =  0 ]; // 系统时间   
    epeated linkcallPK_pkinfo data        = 5 ; // Pk信息	
}
```
###6、用户查询连麦PK主播信息
```seq
note right of client: 用户查询连线状态
client->>server: linkcallPK_user_seek_PK_rq
note right of server:校验数据判空等状态合法性
note right of client: 响应用户查询连线状态
server->>client: linkcallPK_user_seek_PK_rs
```
```
message linkcallPK_user_seek_PK_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 host_sid			  = 1 [default =  0 ]; // 主场房间号
}
message linkcallPK_user_seek_PK_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error       = 1                ; // error info
	required uint64 host_sid		  = 2 [default =  0 ]; // 主场房间号
	required uint64 time_now		  = 3 [default =  0 ]; // 系统时间    
    repeated linkcallPK_pkinfo data   = 5 ; // PK信息
}
```
###7、用户查询连麦PK用户送礼信息
```seq
note right of client: 用户查询连线状态
client->>server: linkcallPK_user_seek_gift_rq
note right of server:校验数据判空等状态合法性
note right of client: 响应用户查询连线状态
server->>client: linkcallPK_user_seek_gift_rs
```
```
message linkcallPK_user_seek_PK_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 host_sid		     = 1 [default =  0 ]; // 主场房间号
}
message linkcallPK_user_seek_PK_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error         = 1         ; // error info
	required uint64 host_sid		    = 2 [default =  0 ]; // 主场房间号
	required uint64 time_now		    = 3 [default =  0 ]; // 系统时间   	
    repeated linkcallPK_user_gift datas = 4         ; // 用户送礼列表
}
```
###8、主播查询连麦PK列表（作为主场的列表和作为客场的列表）
```seq
note right of client: 用户查询连线状态
client->>server: linkcallPK_singer_seek_list_rq
note right of server:校验数据判空等状态合法性
note right of client: 响应用户查询连线状态
server->>client: linkcallPK_singer_seek_list_rs
```
```
message linkcallPK_singer_seek_list_rq
{
	enum msg{ id=0x99990021;} 
	required uint64 host_sid			  = 1 [default =  0 ]; // 主场房间号
}
message linkcallPK_singer_seek_list_rs
{
	enum msg{ id=0x99990022;} 
	required b_error.info error           = 1         ; // error info
	required uint64 host_sid		      = 2 [default =  0 ]; // 主场房间号
	required uint64 time_now		      = 3 [default =  0 ]; // 系统时间 	
	repeated linkcallPK_singer_info datas = 4         ; // 比较全的主播列表信息
}
```
###9、用户进入房间
```
客户端，补发6,7（主播/用户）查询当前最新连麦PK信息
客户端补发linkcallPK_user_seek_PK_rq（最好只发这个）
客户端补发linkcallPK_user_seek_gift_rq（根据主客场id可以查询），最好是用户手动点击在查询
//也可以用户一入场，就查询全部，后续的房间推送linkcallPK_room_pkinfo_nt，客户端自己累加登记用户送礼列表
```
###10、用户退出房间
```
不影响该功能，忽略
```

###11、主播进入直播房间
```
客户端，补发6用户查询最新PK信息，linkcallPK_user_seek_PK_rq，可以拿到最新PK情况
客户端，补发8主播查询最新申请列表，linkcallPK_singer_seek_list_rq，拿到最新主播列表
```
###12、主播关闭直播房间
```seq
note right of mq: 主播离开触发事件
mq->>server: p_user_real_leave_channel_event
note right of server: 主播无连麦PK，无任何变化
note right of server: 执行一次  5主播结束连麦PK功能
note right of server: 删除房间内和主播关联的缓存
```
