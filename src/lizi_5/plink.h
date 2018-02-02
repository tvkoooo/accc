#ifndef PLINK_H_
#define PLINK_H_
#include "common/packet.h"
#include <vector>
#include <iterator>
#include "common/core/base_svid.h"
#include "common/iproperty.h"

#include "psessionbase.h"
//#define FOLDER_SUFFIX "@f"
namespace protocol {

	namespace slist {


		struct helper {
			static bool isFolder(const std::string &id) {
				return atoi(id.data()) < 1000000;
			}
		};

		////////////////////////////////////////////////////////////////
		struct SListItem : public sox::Marshallable {
			enum ItemType {
				chanel,
				folder
			};
			uint32_t sid;
			uint32_t asid;
			uint32_t pid;
			uint32_t users;
			uint32_t owner;
			std::string snick;
			ItemType type;
			bool bLimit;
			bool bPublic;
			std::string intro;
			std::string lables;
			std::string slogan;

			SListItem() :users(0), type(chanel), bPublic(true) {}
			SListItem(int s, int as, int p, const std::string &n, int o, ItemType t, bool l, const std::string &str) :
				sid(s), asid(as), pid(p), users(0), owner(o), snick(n), type(t), bLimit(l), bPublic(true), intro(str) {
			}

			virtual void marshal(sox::Pack &p) const {
				p << sid << asid << pid << users << snick << owner << intro << lables << slogan;
				p.push_uint8(type);
				p.push_uint8(bLimit);
				p.push_uint8(bPublic);

			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> sid >> asid >> pid >> users >> snick >> owner >> intro >> lables >> slogan;
				type = (ItemType)p.pop_uint8();
				bLimit = p.pop_uint8() != 0;
				bPublic = p.pop_uint8() != 0;
			}
		};

	} //slist
	////////////////////////////////////////////////////////////////


	namespace link {

		struct PSyncSList : public sox::Voidmable {//uri:5376
			enum { uri = (21 << 8 | protocol::LINKD_SVID) };
		};


		struct PSyncSListRes : public sox::Marshallable {
			enum { uri = (21 << 8 | protocol::LINKD_SVID) };
			std::vector<protocol::slist::SListItem> items;
			virtual void marshal(sox::Pack &p) const {
				marshal_container(p, items);
			}
			virtual void unmarshal(const sox::Unpack &p) {
				unmarshal_container(p, std::back_inserter(items));
			}
		};


		struct PAddFolder : public sox::Marshallable {
			enum { uri = (22 << 8 | protocol::LINKD_SVID) };
			std::string nick;
			uint32_t pid;
			virtual void marshal(sox::Pack &p) const {
				p << pid << nick;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> pid >> nick;
			}
		};


		struct PAddFolderRes : public sox::Marshallable {
			enum { uri = (22 << 8 | protocol::LINKD_SVID) };
			std::string nick;
			uint32_t fid;
			uint32_t pid;
			virtual void marshal(sox::Pack &p) const {
				p << nick << fid << pid;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> nick >> fid >> pid;
			}
		};


		struct PRemoveFolder : public sox::Marshallable {
			enum { uri = (23 << 8 | protocol::LINKD_SVID) };
			uint32_t fid;
			uint32_t pid;
			virtual void marshal(sox::Pack &p) const {
				p << fid << pid;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> fid >> pid;
			}
		};

		struct PRemoveFolderRes : public PRemoveFolder {
		};

		struct PAddSess : public sox::Marshallable {
			enum { uri = (24 << 8 | protocol::LINKD_SVID) };
			uint32_t sid;
			uint32_t asid;
			uint32_t pid;
			uint32_t owner;
			//todo
			std::string nick;
			std::string lables;
			std::string	slogan;

			bool bLimit;

			virtual void marshal(sox::Pack &p) const {
				std::string	lables;
				p << sid << asid << pid << nick << owner << lables << slogan;
				p.push_uint8(bLimit);
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> sid >> asid >> pid >> nick >> owner >> lables >> slogan;
				bLimit = p.pop_uint8() != 0;
			}
		};

		struct PAddSessRes : public PAddSess {
			//if pid has child more them one, onchange
		};


		struct PRemoveSess : public sox::Marshallable {
			enum { uri = (25 << 8 | protocol::LINKD_SVID) };
			uint32_t sid;
			uint32_t pid;
			virtual void marshal(sox::Pack &p) const {
				p << sid << pid;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> sid >> pid;
			}
		};

		struct PRemoveSessRes : public PRemoveSess {
			//if pid is empty remove else onchange happen
		};

		struct PGetUInfoByAccount : public sox::Marshallable
		{//uri:6656
			enum { uri = (26 << 8) | LINKD_SVID };
			std::string account;
			uint32_t ver;

			PGetUInfoByAccount() {}
			PGetUInfoByAccount(const std::string &u, uint32_t _v = 0) :account(u), ver(_v) { }


			virtual void marshal(sox::Pack &p) const { p << ver << account; }
			virtual void unmarshal(const sox::Unpack &p) { p >> ver >> account; }
			virtual std::ostream &trace(std::ostream &os) const { return os << "ver = " << ver; }
		};

		struct PRepGetUInfo : public sox::Properties
		{
			enum { uri = (27 << 8) | LINKD_SVID };
		};

		struct PUpdateUInfo : public sox::Properties
		{
			enum { uri = (28 << 8) | LINKD_SVID };
		};

		struct PRepUpdateUInfo : public PUpdateUInfo {
		};

		struct PReportSpy : public sox::Marshallable {
			enum { uri = (29 << 8 | protocol::LINKD_SVID) };
			std::string mac;
			std::string localip;
			bool hasUT;
			bool hasIS;

			virtual void marshal(sox::Pack &pk) const {
				pk << mac << localip << (uint16_t)hasUT << (uint16_t)hasIS;
			}

			virtual void unmarshal(const sox::Unpack &up) {
				up >> mac >> localip;
				hasUT = up.pop_uint16() != 0;
				hasIS = up.pop_uint16() != 0;
			}
		};

		struct PGetUInfoByUid : public sox::Marshallable
		{//uri:7680
			enum { uri = (30 << 8) | LINKD_SVID };
			PGetUInfoByUid() {}
			PGetUInfoByUid(uint32_t u, uint32_t _v = 0) :uid(u), ver(_v) { }

			uint32_t uid;
			uint32_t ver;

			virtual void marshal(sox::Pack &p) const { p << ver << uid; }
			virtual void unmarshal(const sox::Unpack &p) { p >> ver >> uid; }
			virtual std::ostream &trace(std::ostream &os) const { return os << "ver = " << ver; }
		};

		struct PGetUid : public sox::Marshallable
		{
			enum { uri = (31 << 8) | LINKD_SVID };
			std::string account;
			virtual void marshal(sox::Pack &pk) const {
				pk << account;
			}

			virtual void unmarshal(const sox::Unpack &up) {
				up >> account;
			}

		};

		struct PRepGetUid : public sox::Marshallable
		{
			enum { uri = (32 << 8) | LINKD_SVID };
			uint32_t uid;
			std::string account;
			virtual void marshal(sox::Pack &pk) const {
				pk << uid << account;
			}

			virtual void unmarshal(const sox::Unpack &up) {
				up >> uid >> account;
			}
		};


		struct PSyncSInfo : public sox::Marshallable {
			enum { uri = (33 << 8 | LINKD_SVID) };
			uint32_t sid;
			void marshal(sox::Pack & pk) const {
				pk << sid;
			}
			void unmarshal(const sox::Unpack &up) {
				up >> sid;
			}
		};

		struct PSyncSInfoRes : public sox::Properties {
			enum { uri = (34 << 8 | LINKD_SVID) };
		};


		struct PSessionLbs : public sox::Marshallable {
			enum { uri = (35 << 8 | protocol::LINKD_SVID) };
			ISPType type;
			uint32_t sid;
			PSessionLbs() {
			}
			PSessionLbs(uint32_t s) :sid(s) {
			}
			virtual void marshal(sox::Pack &p) const {
				p << sid;
				p.push_uint16(type);
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> sid;
				type = (ISPType)p.pop_uint16();
			}

		};


		struct PSessionLbsRes : public sox::Marshallable {
			enum { uri = (36 << 8 | protocol::LINKD_SVID) };
			uint32_t sid;
			uint32_t asid;
			std::string ip;
			std::vector<uint16_t> ports;
			std::string key;
			virtual void marshal(sox::Pack &p) const {
				p << sid << asid << ip << key;
				sox::marshal_container(p, ports);
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> sid >> asid >> ip >> key;
				sox::unmarshal_container(p, std::back_inserter(ports));
			}
		};

		struct PSyncSubListVec : public  sox::Marshallable {
			enum { uri = (37 << 8 | protocol::LINKD_SVID) };
			std::vector<uint32_t> pids;
			virtual void marshal(sox::Pack &p) const {
				marshal_container(p, pids);
			}
			virtual void unmarshal(const sox::Unpack &p) {
				unmarshal_container(p, std::back_inserter(pids));
			}
		};

		struct PSyncSubListVecRes : public sox::Marshallable {
			enum { uri = (38 << 8 | protocol::LINKD_SVID) };
			uint32_t pid;
			std::vector<protocol::slist::SListItem> items;
			virtual void marshal(sox::Pack &p) const {
				p << pid;
				marshal_container(p, items);
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> pid;
				unmarshal_container(p, std::back_inserter(items));
			}
		};


		struct PGetFond : public sox::Marshallable {
			enum { uri = (39 << 8 | protocol::LINKD_SVID) };
			uint32_t sid;
			uint32_t pid;
			bool	fond;


			virtual void marshal(sox::Pack &p) const {
				p << sid << pid << fond;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> sid >> pid >> fond;
			}
		};




		struct PUpdateFondRes : public PAddSess {
			enum { uri = (39 << 8 | protocol::LINKD_SVID) };
			bool	bFond;


			virtual void marshal(sox::Pack &p) const {
				PAddSess::marshal(p);
				p << bFond;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				PAddSess::unmarshal(p);
				p >> bFond;
			}
		};

		struct PRepGetUserInSess : public sox::Marshallable {
			enum { uri = (40 << 8 | protocol::LINKD_SVID) };
			uint32_t uid;
			uint32_t pid;
			virtual void marshal(sox::Pack &p) const {
				p << uid << pid;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> uid >> pid;
			}
		};


		struct PSUserInSess : public sox::Marshallable {
			uint32_t sid;
			uint32_t contribute;
			protocol::session::ChannelRoler	 roler;
			std::string sname;
			virtual void marshal(sox::Pack &p) const {
				p << sid << contribute << sname;
				p.push_uint16(roler);
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> sid >> contribute >> sname;
				roler = (protocol::session::ChannelRoler)p.pop_uint16();
			}
		};

		struct PRepGetUserInSessRes : public sox::Marshallable {
			enum { uri = (40 << 8 | protocol::LINKD_SVID) };
			uint32_t uid;
			uint32_t pid;
			std::vector<PSUserInSess> userInSess;

			virtual void marshal(sox::Pack &p) const {
				std::string	lables;
				p << uid << pid;
				marshal_container(p, userInSess);
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> uid >> pid;
				unmarshal_container(p, std::back_inserter(userInSess));
			}
		};

		struct PEffect : public sox::Marshallable {
			uint32_t eid;
			uint32_t sid;
			uint32_t type;
			uint32_t start_time;
			uint32_t end_time;
			virtual void marshal(sox::Pack &p) const {
				p << eid << sid << type << start_time << end_time;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> eid >> sid >> type >> start_time >> end_time;
			}
		};



		struct PGetEffect : public sox::Marshallable {
			enum { uri = (41 << 8 | protocol::LINKD_SVID) };
			uint32_t uid;
			virtual void marshal(sox::Pack &p) const {
				p << uid;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> uid;
			}
		};

		struct PGetEffectRes : public sox::Marshallable {
			enum { uri = (41 << 8 | protocol::LINKD_SVID) };
			uint32_t uid;
			std::vector<PEffect> effects;
			virtual void marshal(sox::Pack &p) const {
				p << uid;
				sox::marshal_container(p, effects);
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> uid;
				sox::unmarshal_container(p, std::back_inserter(effects));
			}
		};

		struct PDisableSelfEffect : public sox::Marshallable {
			enum { uri = (42 << 8 | protocol::LINKD_SVID) };
			uint32_t eid;
			uint32_t type;
			virtual void marshal(sox::Pack &p) const {
				p << eid << type;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> eid >> type;
			}
		};

		struct PUpdateFond : public sox::Marshallable {
			enum { uri = (43 << 8 | protocol::LINKD_SVID) };
			uint32_t sid;
			uint32_t pid;
			bool	fond;


			virtual void marshal(sox::Pack &p) const {
				std::string	lables;
				p << sid << pid << fond;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> sid >> pid >> fond;
			}
		};


		struct PGetFondRes : public PGetFond {
			enum { uri = (44 << 8 | protocol::LINKD_SVID) };
		};


		/*
		struct PGetfEffectInfo : public sox::Marshallable {
			enum {uri = (41 << 8 | protocol::LINKD_SVID)};
			uint32_t eid;
			uint32_t type;
			virtual void marshal(sox::Pack &p) const {
				p << eid << type;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> eid >> type;
			}
		};

		struct PGetEffectInfoRes : public sox::Marshallable {
			enum {uri = (41 << 8 | protocol::LINKD_SVID)};
			uint32_t eid;
			uint32_t type;
			std::string info;
			std::string tinfo;
			virtual void marshal(sox::Pack &p) const {
				p << eid << type << info << tinfo;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> eid >> type >> info >> tinfo;
			}
		};
		*/

		struct PBattleLbs : public sox::Marshallable {
			enum { uri = (50 << 8 | protocol::LINKD_SVID) };
			ISPType type;
			uint32_t uid; //自己的uid
			uint32_t battleId; //战斗的id,如果不知道或者获取不了，填0

			PBattleLbs() {
			}

			virtual void marshal(sox::Pack &p) const {
				p.push_uint16(type);
				p << uid << battleId;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				type = (ISPType)p.pop_uint16();
				p >> uid >> battleId;
			}
		};


		struct PBattleLbsRes : public sox::Marshallable {
			enum { uri = (51 << 8 | protocol::LINKD_SVID) };

			std::string ip;
			std::vector<uint16_t> ports;

			virtual void marshal(sox::Pack &p) const {
				p << ip;
				sox::marshal_container(p, ports);
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> ip;
				sox::unmarshal_container(p, std::back_inserter(ports));
			}
		};

		struct PForwardBattleChallenge : public sox::Marshallable {
			enum { uri = (52 << 8 | protocol::LINKD_SVID) };

			uint32_t aUid; //发起挑战方用户A uid
			std::string aNickName; //发起挑战方用户A的昵称
			uint32_t bUid; //被挑战方用户B uid
			uint16_t source; //用户点击哪里发起这个战斗请求的：0-用户小资料卡，1-用户个人主页，2-公屏聊天，3-私聊
			uint32_t battleId; //战斗id,用于标识一场战斗
			uint32_t aConnectId; //发起挑战方用户A的连接id

			std::string battleIp;//战斗服ip
			uint32_t battlePort;//战斗服端口

			virtual void marshal(sox::Pack &p) const {
				p << aUid << aNickName << bUid << source << battleId << aConnectId << battleIp << battlePort;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> aUid >> aNickName >> bUid >> source >> battleId >> aConnectId >> battleIp >> battlePort;;
			}
		};

		struct PForwardBattleResult : public sox::Marshallable {
			enum { uri = (53 << 8 | protocol::LINKD_SVID) };

			uint32_t toUid; //该协议要转发的用户uid

			uint8_t winerSide; //胜利方: 0 - 己方；1 - 敌方
			uint32_t winerUid; //胜利方玩家uid

			virtual void marshal(sox::Pack &p) const {
				p << toUid << winerSide << winerUid;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				p >> toUid >> winerSide >> winerUid;
			}
		};


		struct PForwardBattleChallengeRes : public PForwardBattleChallenge {
			enum { uri = (54 << 8 | protocol::LINKD_SVID) };

			uint8_t result; //0-成功，可以开战；1 - 对方不存在或不在线

			virtual void marshal(sox::Pack &p) const {
				PForwardBattleChallenge::marshal(p);
				p << result;
			}
			virtual void unmarshal(const sox::Unpack &p) {
				PForwardBattleChallenge::unmarshal(p);
				p >> result;
			}

		};

		struct PForwardBattleResultRes : public PForwardBattleResult {
			enum { uri = (55 << 8 | protocol::LINKD_SVID) };
		};

		struct PPingFromSession : public sox::Marshallable {
			enum {uri = (56 << 8) | protocol::LINKD_SVID};
			uint32_t type;
			virtual void marshal(sox::Pack &pk) const {
				pk << type;
			}
			virtual void unmarshal(const sox::Unpack &up) {
				up >> type;
			}
		};

		//战斗服相关业务透传协议请求
	    struct PBattleTransferByLinkd: public sox::Marshallable{
	        enum { uri = (57 << 8 | protocol::LINKD_SVID) };
			
	        std::string requestData;//向战斗服请求的信息
	        
	        virtual void marshal(sox::Pack &p) const {
	            p << requestData;
	        }
	        virtual void unmarshal(const sox::Unpack &p) {
	            p >>requestData;
	        }
	    };


		//战斗服相关业务透传协议响应
	    struct RBattleTransferByLinkd: public sox::Marshallable{
	        enum { uri = (57 << 8 | protocol::LINKD_SVID) };
			
	        std::string repondData;//战斗服响应的信息
	        
	        virtual void marshal(sox::Pack &p) const {
	            p << repondData;
	        }
	        virtual void unmarshal(const sox::Unpack &p) {
	            p >> repondData;
	        }
	    };

		//战斗服业务通过linkd响应的协议
	    struct PBattleResInfoToClientByLinkd: public sox::Marshallable{
	        enum { uri = (59 << 8 | protocol::LINKD_SVID) };

			uint32_t uid;
	        std::string data;//战斗服响应的信息
	        
	        virtual void marshal(sox::Pack &p) const {
	            p << uid << data;
	        }
	        virtual void unmarshal(const sox::Unpack &p) {
	            p >> uid >> data;
	        }
	    };
		
	}
}
#endif /*PSLIST_H_*/
