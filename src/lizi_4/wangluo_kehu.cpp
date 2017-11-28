#include<WINSOCK2.H>  
#include<STDIO.H>  
#include<iostream>  
#include<cstring>  
#include "wangluo.h"
#include <ws2tcpip.h>
using namespace std;  
#pragma comment(lib, "ws2_32.lib")  

static LPSTR ConvertErrorCodeToString(DWORD ErrorCode)
{
	HLOCAL LocalAddress=NULL;
	FormatMessage(FORMAT_MESSAGE_ALLOCATE_BUFFER|FORMAT_MESSAGE_IGNORE_INSERTS|FORMAT_MESSAGE_FROM_SYSTEM,NULL,ErrorCode,0,(PTSTR)&LocalAddress,0,NULL);
	return (LPSTR)LocalAddress;
}

void *lizhi4_wangluo_kehu(void *)
{  
	/////����Winsock��
	WORD sockVersion = MAKEWORD(2, 2);  
	WSADATA data;  
	if(WSAStartup(sockVersion, &data)!=0)  
	{  
		return NULL;  
	}  

	while(true){  
		SOCKET sclient = socket(PF_INET6, SOCK_STREAM, IPPROTO_TCP);  
		if(sclient == INVALID_SOCKET)  
		{  
			printf("invalid socket!");  
		return NULL;  
		}  

		sockaddr_in6 serAddr; 
		memset(&serAddr,0,sizeof(sockaddr_in6));		
		serAddr.sin6_family = AF_INET6;  
		serAddr.sin6_port = htons(8888);
		//inet_pton(AF_INET, "192.168.111.203", &serAddr.sin_addr);
		inet_pton(AF_INET6, "::1", &serAddr.sin6_addr);

		int err_x;
		err_x=connect(sclient, (sockaddr *)&serAddr, sizeof(serAddr));
		if(SOCKET_ERROR==err_x)  
		{  //����ʧ��  
					int err = GetLastError();
			printf("connect error %s",ConvertErrorCodeToString(err));  
			closesocket(sclient);  
		return NULL;  
		}  

		char data[20]="00000000";
		cin>>data;  
		const char * sendData;  
		sendData = data;   //stringתconst char*   
		//char * sendData = "��ã�TCP����ˣ����ǿͻ���\n";  
		send(sclient, sendData, strlen(sendData), 0);  
		//send()������������ָ����socket�����Է�����  
		//int send(int s, const void * msg, int len, unsigned int flags)  
		//sΪ�ѽ��������ӵ�socket��msgָ���������ݣ�len��Ϊ���ݳ��ȣ�����flagsһ����0  
		//�ɹ��򷵻�ʵ�ʴ��ͳ�ȥ���ַ�����ʧ�ܷ���-1������ԭ�����error   

		char recData[255];  
		int ret = recv(sclient, recData, 255, 0);  
		if(ret>0){  
			recData[ret] = 0x00;  
			printf(recData);  
		}   
		closesocket(sclient);  
	}  

	////�ͷ�Winsock��
	WSACleanup();  
		return NULL;  

}  