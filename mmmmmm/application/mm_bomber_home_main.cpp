#include "mm_bomber_home_main.h"


#include "core/mm_logger.h"
//
//#include "dish/mm_file_context.h"
//#include "dish/mm_lua_context.h"
//#include "lua/lua_tinker.h"
//
#include "CEGUI/System.h"
#include "CEGUI/GUIContext.h"
#include "CEGUI/SchemeManager.h"
#include "CEGUI/WindowManager.h"
#include "CEGUI/widgets/DefaultWindow.h"

#include "CEGUI/XMLParserModules/TinyXML/XMLParserModule.h"

//
//#include "mm_cegui_ogre_renderer/ImageCodec.h"
//#include "mm_cegui_ogre_renderer/ResourceProvider.h"

//#include "OgreCamera.h"
//#include "OgreSceneNode.h"
//#include "OgreEntity.h"
//#include "OgreViewport.h"
//#include "OgreMaterialManager.h"
//
//#include "flake/mm_flake_context.h"
//#include "flake/mm_flake_surface.h"
//
//#include "net/mm_sockaddr.h"
//#include "net/mm_mailbox.h"

//#include "openssl/rsa.h"
//#include "openssl/bn.h"
//#include "openssl/ossl_typ.h"
//
//#include "crypto/rsa/rsa_locl.h"

//#include "tinyxml.h"


//////////////////////////////////////////////////////////////////////////

namespace mm
{

	//////////////////////////////////////////////////////////////////////////
	bomber_home_main::bomber_home_main()
		: show_text("Wo men dou shi da SB")
		,reconfirm_flag(0)
		,boy_x(0)
		,boy_y(0)
		,boy_z(0)
		//,bomber_home_main_exit1(NULL)
		, d_window(NULL)
		, l_ensure(NULL)
		, l_home_main(NULL)

		,_frameWindow(NULL)
		,_reconfirmWindow(NULL)
		,_reconfirmWindow1(NULL)

		,l_ensure_Label_table(NULL)
		,l_ensure_Button_exit(NULL)
		,l_ensure_Button_login(NULL)
		,l_ensure_Button_apply(NULL)
		,l_ensure_Editbox_username(NULL)
		,l_ensure_Editbox_password(NULL)

		,l_Label_reconfirm(NULL)
		,l_Button_yes(NULL)
		,l_Button_back(NULL)

		,l_Label_reconfirm1(NULL)
		,l_Button_yes1(NULL)
		,l_Button_back1(NULL)
		
	{
		//this->bomber_home_main_exit1=&(this->bomber_home_main_exit);
	}

	bomber_home_main::~bomber_home_main()
	{

	}

	void bomber_home_main::mm_flake_context_assignment(mm_flake_context* p_flake_context)
	{
		this->flake_context_home_main=p_flake_context;
		
	}


		void bomber_home_main::bomber_home_main_launching( mm_flake_surface* surface )
		{

			

			struct mm_logger* g_logger = mm_logger_instance();
			mm_logger_log_I(g_logger,"%s %d 1.",__FUNCTION__,__LINE__);
			//////////////////////////////////////////////////////////////////////////
			//mm_flake_context* flake_context = this->get_context();
			//CEGUI::System* _cegui_system_ptr = flake_context->d_cegui_system.d_system;
			//mm_flake_surface* flake_surface = flake_context->get_main_flake_surface();
			//Ogre::RenderWindow* render_window = flake_surface->get_render_window();
			//////////////////////////////////////////////////////////////////////////
			int index = 0;
			//float d = 0;
			char index_string[64] = {0};
			mm_sprintf(index_string,"_%d", index);
			////////////////////////////////////////////////////////////////////
			CEGUI::GUIContext* _gui_context = surface->d_gui_context;
			//CEGUI::GUIContext* _gui_context = &_cegui_system_ptr->getDefaultGUIContext();
			//CEGUI::GUIContext* _gui_context = surface->d_gui_context;
			//flake_context->d_cegui_system.set_system_default_ttf_source("Miui-Bold.ttf");
			//flake_context->d_cegui_system.set_system_default_ttf_source("Miui-Regular.ttf");
			//flake_context->d_cegui_system.set_system_default_ttf_source("NotoSansSC-Regular.otf");
			// here we create a default font.		
			std::string system_font_12_name = "system_font_12";
			system_font_12_name = system_font_12_name + index_string;
			CEGUI::Font& _defaultFont = this->flake_context_home_main->d_cegui_system.create_system_font_by_size(surface, system_font_12_name.c_str(), 12.0f);

			// The next thing we do is to set a default mouse cursor image.  This is
			// not strictly essential, although it is nice to always have a visible
			// cursor if a window or widget does not explicitly set one of its own.
			//
			// The TaharezLook Imageset contains an Image named "MouseArrow" which is
			// the ideal thing to have as a defult mouse cursor image.
			_gui_context->getMouseCursor().setDefaultImage("TaharezLook/MouseArrow");

			// load font and setup default if not loaded via scheme
			// Set default font for the gui context
			_gui_context->setDefaultFont(&_defaultFont);
			////////////////////////////////////////////////////////////////////////////
			CEGUI::WindowManager& _winMgr = CEGUI::WindowManager::getSingleton();
			std::string root_window_name = "root_window";
			root_window_name = root_window_name + index_string;
			this->d_window = (CEGUI::Window*)_winMgr.createWindow("DefaultWindow", root_window_name.c_str());
			//CEGUI::Window* l_home_page_main = _winMgr.loadLayoutFromFile("l_home_page_main.layout");
			//CEGUI::Window* l_home_page_account = _winMgr.loadLayoutFromFile("l_home_page_account.layout");

			this->l_ensure = _winMgr.loadLayoutFromFile("l_ensure.layout");
			this->l_home_main = _winMgr.loadLayoutFromFile("l_home_main.layout");
			this->l_ensure->setName("l_ensure0");
			this->l_home_main->setName("l_home_main0");

			this->l_reconfirm = _winMgr.loadLayoutFromFile("l_reconfirm.layout");
			this->l_reconfirm1 = _winMgr.loadLayoutFromFile("l_reconfirm.layout");
			this->l_reconfirm->setName("l_reconfirm0");//重设名字，防止加载同一个layout会重名出错
			this->l_reconfirm1->setName("l_reconfirm1");

			//CEGUI::Window* l_title = _winMgr.loadLayoutFromFile("l_title.layout");
			//l_title->addChild(l_home_page_account);
			//l_home_page_main->addChild(l_title);
			//w->addChild(l_home_page_main);
			//w->addChild(l_home_page_account);

			this->d_window->addChild(this->l_ensure);
			this->d_window->addChild(this->l_home_main);
			this->d_window->addChild(this->l_reconfirm);
			this->d_window->addChild(this->l_reconfirm1);


			_gui_context->setRootWindow(this->d_window);



			//////////////////////////////////////////////////////////////////////////
			_frameWindow = this->l_ensure->getChild("FrameWindow");
			l_ensure_Label_table = _frameWindow->getChild("Label_table");
			l_ensure_Button_exit = _frameWindow->getChild("Button_exit");
			l_ensure_Button_login = _frameWindow->getChild("Button_login");
			l_ensure_Button_apply = _frameWindow->getChild("Button_apply");
			l_ensure_Editbox_username = _frameWindow->getChild("Editbox_username");
			l_ensure_Editbox_password = _frameWindow->getChild("Editbox_password");

			l_ensure_Label_table ->setText(this->show_text);
			this->l_ensure_Button_exit_conn = l_ensure_Button_exit->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_home_main::on_handle_l_ensure_exit_clicked, this));
			this->l_ensure_Button_login_conn = l_ensure_Button_login->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_home_main::on_handle_l_ensure_login_clicked, this));
			this->l_ensure_Button_apply_conn = l_ensure_Button_apply->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_home_main::on_handle_l_ensure_apply_clicked, this));
			//////////////////////////////////////////////////////////////////////////
			CEGUI::Window* _StaticImage = this->l_home_main->getChild("StaticImage");
			CEGUI::Window* _l_trolley = this->l_home_main->getChild("l_trolley");
			CEGUI::Window* l_home_main_Button_trolley = _l_trolley->getChild("Button_trolley");
			CEGUI::Window* l_home_main_Button_cancel = _l_trolley->getChild("Button_cancel");
			this->l_home_main_trolley_conn = l_home_main_Button_trolley->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_home_main::on_handle_l_home_main_Button_trolley_clicked, this));
			this->l_home_main_cancel_conn = l_home_main_Button_cancel->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_home_main::on_handle_l_home_main_Button_cancel_clicked, this));

			//////////////////////////////////////////////////////////////////////////
			_reconfirmWindow = this->l_reconfirm->getChild("reconfirmWindow");
			l_Label_reconfirm = _reconfirmWindow->getChild("Label_reconfirm");
			l_Button_yes = _reconfirmWindow->getChild("Button_yes");
			l_Button_back = _reconfirmWindow->getChild("Button_back");
			this->l_Button_yes_conn = l_Button_yes->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_home_main::on_handle_l_reconfirm_Button_yes_clicked, this));
			this->l_Button_back_conn = l_Button_back->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_home_main::on_handle_l_reconfirm_Button_back_clicked, this));
			//////////////////////////////////////////////////////////////////////////////
			_reconfirmWindow1 = this->l_reconfirm1->getChild("reconfirmWindow");
			l_Label_reconfirm1 = _reconfirmWindow1->getChild("Label_reconfirm");
			l_Button_yes1 = _reconfirmWindow1->getChild("Button_yes");
			l_Button_back1 = _reconfirmWindow1->getChild("Button_back");
			this->l_Button_yes_conn1 = l_Button_yes1->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_home_main::on_handle_l_reconfirm_Button_yes1_clicked, this));
			this->l_Button_back_conn1 = l_Button_back1->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_home_main::on_handle_l_reconfirm_Button_back1_clicked, this));
			//////////////////////////////////////////////////////////////////////////////


			this->l_ensure->setVisible(1);

			this->l_home_main->setVisible(0);
			this->l_reconfirm->setVisible(0);
			this->l_reconfirm1->setVisible(0);

			do 
			{
				std::ifstream user_info_map;
				user_info_map.open("user_info.daSB");
				if(user_info_map.fail())
				{
					break;
				}				
				while(!user_info_map.eof())
				{
					std::string key;
					std::string value;
					user_info_map>>key>>value;
					if (key!="")
					{
						this->user_info.insert(std::map<std::string,std::string>::value_type(key,value));
					}

				}
				user_info_map.close();
			} while (0);

		}

	void bomber_home_main::bomber_home_main_terminate( mm_flake_surface* surface )
	{
		struct mm_logger* g_logger = mm_logger_instance();
		CEGUI::WindowManager& _window_manager = CEGUI::WindowManager::getSingleton();

		this->l_Button_yes_conn->disconnect();
		this->l_Button_back_conn->disconnect();

		this->l_ensure_Button_exit_conn->disconnect();
		this->l_ensure_Button_login_conn->disconnect();

		this->l_ensure_Button_apply_conn->disconnect();

		this->l_home_main_trolley_conn->disconnect();
		this->l_home_main_cancel_conn->disconnect();

		this->d_window->removeChild(this->l_reconfirm);
		this->d_window->removeChild(this->l_ensure);
		this->d_window->removeChild(this->l_home_main);

		_window_manager.destroyWindow(this->l_reconfirm);
		_window_manager.destroyWindow(this->l_ensure);
		_window_manager.destroyWindow(this->l_home_main);

		_window_manager.destroyWindow(this->d_window);

		mm_logger_log_I(g_logger,"mm_bomber::%s %d success.",__FUNCTION__,__LINE__);
	}

	bool bomber_home_main::on_handle_l_ensure_exit_clicked(const CEGUI::EventArgs& args)
	{
		mm_flake_context* flake_context = this->flake_context_home_main;
		//flake_context->shutdown();
		this->l_ensure->setVisible(0);
		this->l_reconfirm->setVisible(1);
		return false;
	}

	bool bomber_home_main::on_handle_l_ensure_login_clicked(const CEGUI::EventArgs& args)
	{
		mm_flake_context* flake_context = this->flake_context_home_main;

		std::string user_name=l_ensure_Editbox_username->getText().c_str();
		std::string pass_word=l_ensure_Editbox_password->getText().c_str();		

		std::map<std::string,std::string>::iterator it;
		it= this->user_info.find(user_name);
		if (it == this->user_info.end())
		{
			//没有找到
			l_ensure_Label_table->setText("You haven't applied this yet");
		}
		else
		{
			//找到
			if (it->second!=pass_word)
			{
				l_ensure_Label_table->setText("The password error");
			} 
			else
			{
				this->l_ensure->setVisible(0);
				this->l_home_main->setVisible(1);
			}

		}
		return false;
	}

	bool bomber_home_main::on_handle_l_ensure_apply_clicked(const CEGUI::EventArgs& args)
	{
		mm_flake_context* flake_context = this->flake_context_home_main;


		std::string user_name=l_ensure_Editbox_username->getText().c_str();
		std::string pass_word=l_ensure_Editbox_password->getText().c_str();
		std::map<std::string,std::string>::iterator it= this->user_info.find(user_name);
		if (it == this->user_info.end())
		{
			//没有找到
			l_ensure_Label_table->setText("Welcome SB Club,you sure to apply");
			//this->user_info.insert(std::map<std::string,std::string>::value_type(user_name,pass_word));

			//std::ofstream user_info_map;
			//user_info_map.open("user_info.daSB",std::ofstream::app);
			//user_info_map<<user_name<<"\t"<<pass_word<<std::endl;
			//user_info_map.close();
			this->l_ensure->setVisible(0);
			this->l_reconfirm1->setVisible(1);
		}
		else
		{
			//找到
			l_ensure_Label_table->setText("You are late,NC");
		}

		return false;
	}


	bool bomber_home_main::on_handle_l_home_main_Button_trolley_clicked(const CEGUI::EventArgs& args)
	{
		mm_flake_context* flake_context = this->flake_context_home_main;
		flake_context->shutdown();

		return false;
	}

	bool bomber_home_main::on_handle_l_home_main_Button_cancel_clicked(const CEGUI::EventArgs& args)
	{
		mm_flake_context* flake_context = this->flake_context_home_main;
		this->l_ensure->setVisible(1);
		this->l_home_main->setVisible(0);
		return false;
	}

	bool bomber_home_main::on_handle_l_reconfirm_Button_yes_clicked(const CEGUI::EventArgs& args)
	{
		mm_flake_context* flake_context = this->flake_context_home_main;
		flake_context->shutdown();

		return false;
	}

	bool bomber_home_main::on_handle_l_reconfirm_Button_back_clicked(const CEGUI::EventArgs& args)
	{
		mm_flake_context* flake_context = this->flake_context_home_main;

		this->l_ensure->setVisible(1);
		this->l_reconfirm->setVisible(0);
		return false;
	}

	bool bomber_home_main::on_handle_l_reconfirm_Button_yes1_clicked(const CEGUI::EventArgs& args)
	{
		mm_flake_context* flake_context = this->flake_context_home_main;


		std::string user_name=l_ensure_Editbox_username->getText().c_str();
		std::string pass_word=l_ensure_Editbox_password->getText().c_str();
		this->user_info.insert(std::map<std::string,std::string>::value_type(user_name,pass_word));

		std::ofstream user_info_map;
		user_info_map.open("user_info.daSB",std::ofstream::app);
		user_info_map<<user_name<<"\t"<<pass_word<<std::endl;
		user_info_map.close();

		this->l_ensure->setVisible(1);
		this->l_reconfirm1->setVisible(0);
		return false;
	}

	bool bomber_home_main::on_handle_l_reconfirm_Button_back1_clicked(const CEGUI::EventArgs& args)
	{
		mm_flake_context* flake_context = this->flake_context_home_main;
		this->l_ensure->setVisible(1);
		this->l_reconfirm1->setVisible(0);
		return false;
	}





	//void bomber_home_main::bomber_home_main_fuzhi(bomber_home_main_back f)
	//{
	//	this->bomber_home_main_exit1=f;
	//
	//}


}
