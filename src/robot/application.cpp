#include "application.h"


static void* __static_rtrr(void* arg)
{

}

void application_init(struct application* p)
{
	llog_instance(log);
	llog_init(log);
	//³ÌÐòÈÕÖ¾
	if (-1 == check_dir("./testlog",0))
	{
		create_dir("./testlog");
	}
	std::string path_1,path_2;
	path_1 = Get_Current_Date();
	path_2 = "./testlog/" + path_1;
	if (-1 == check_dir(path_2.c_str(),0))
	{
		create_dir(path_2.c_str());
	}
	llog_set_log_level(log,LOG_DEBUG);
	llog_open(log,(path_2 + "/mm.log").c_str());
	llog_Info(log,"file:%s line:%d fun:%s ==>> Program log start!\n",__FILE__,__LINE__,__FUNCTION__);	

	lj_data_slot_init(&p->d_slot);
	//test llog
	{

		//lj_data_slot_set_data_slot(&p->d_slot,1,10);
		//char add_1[7] = "zu-iA";
		//lj_data_slot_add_data_a_cup(&p->d_slot,strlen(add_1));
		//memcpy((p->d_slot).data_fp + (p->d_slot).len_remove + (p->d_slot).len_data, add_1,strlen(add_1));
		//(p->d_slot).len_data += strlen(add_1);
		//lj_data_slot_printf_data_slot(&p->d_slot);
		//lj_data_slot_add_data_a_cup(&p->d_slot,strlen(add_1));
		//memcpy(d.data_fp + d.len_remove + d.len_data, add_1,strlen(add_1));
		//d.len_data += strlen(add_1);
		//d.len_remove += 8;
		//d.len_data -= 8;
		//d.printf_data_slot();
		//d.add_data_a_cup(strlen(add_1));
		//d.printf_data_slot();
		//memcpy(d.data_fp + d.len_remove + d.len_data, add_1,strlen(add_1));
		//d.len_data += strlen(add_1);
		//d.printf_data_slot();
		//d.add_data_a_cup(strlen(add_1));
		//memcpy(d.data_fp + d.len_remove + d.len_data, add_1,strlen(add_1));
		//d.len_data += strlen(add_1);
		//d.printf_data_slot();
	}

	{
		uint8_t a = 254;
		char ac = a;
		printf("a=%d ac=%d\n",a,ac);

		uint32_t u64 = 0;
		uint32_t get_u64;
		char u64_0 = 2;
		char u64_1 = 1;

		memcpy((char*)&u64,&u64_0,sizeof(char));
		memcpy((char*)&u64+1,&u64_1,sizeof(char));
		printf("u64=%d \n",u64);

		get_u64 = (uint32_t)(u64_0) | (uint32_t)(u64_1)<<8;
		printf("u64=%d \n",u64);


		_byteswap_ushort(0);


	}


	ltcp_conn_init(&p->conn);

}
void application_destroy(struct application* p)
{
	lj_data_slot_destroy(&p->d_slot);
	ltcp_conn_destroy(&p->conn);

	llog_instance(log);
	llog_Info(log,"program log close!\n\n\n");
	llog_destroy(log);
}

void application_start(struct application* p)
{	
	//ltcp_conn_link(&p->conn,p->argv[1],p->argv[2]);
}
void application_interrupt(struct application* p)
{

	
}
void application_shutdown(struct application* p)
{



}
void application_join(struct application* p)
{

}

void application_fuzhi(struct application* p,int argc,char **argv)
{
	const char s[2] = "-";
	p->argc = argc;
	p->argv = argv;

	if (argc < 6)
	{
		p->log_path = "/home/longjia/c_test/log/vnc_robot_1";
		p->log_level = 7;
		p->instance = "1";
		p->service_num = "1";
		p->object_net = "59.110.125.134";
		p->object_port = 30301;
	} 
	else
	{
		p->log_path = argv[1];
		p->log_level = atoi(argv[2]);
		p->instance = argv[3];
		p->service_num = argv[4];
		p->object_net = strtok(argv[5], s);
		p->object_port = atoi(strtok(NULL, s));
	}
	
	llog_I("System Configuration::\n log_path:%s \n log_level:%d \n instance:%s \n service_num:%s \n object_net:%s \n object_port:%d \n ",p->log_path,p->log_level,p->instance,p->service_num,p->object_net,p->object_port);
}