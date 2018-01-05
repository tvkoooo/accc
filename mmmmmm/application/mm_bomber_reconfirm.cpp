#include "mm_bomber_reconfirm.h"


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
	bomber_reconfirm::bomber_reconfirm()
		: bomber_reconfirm_funhui_1(NULL)
		//, d_window(NULL)
		, l_reconfirm(NULL)
		,_frameWindow(NULL)
		,l_Label_reconfirm(NULL)
		,l_Button_yes(NULL)
		,l_Button_back(NULL)
	{

	}

	bomber_reconfirm::~bomber_reconfirm()
	{

	}
	static void funhui_diaoyong(bomber_reconfirm *)
	{

	}
	void  bomber_reconfirm::bomber_reconfirm_assignment(bomber_reconfirm_huidiao_type fun)
	{
		this->bomber_reconfirm_funhui_1=fun;
	}

	void bomber_reconfirm::mm_flake_context_assignment(mm_flake_context* p_flake_context)
	{
		this->flake_context_reconfirm=p_flake_context;
	}


	void bomber_reconfirm::bomber_reconfirm_launching( mm_flake_surface* surface )
	{
		//struct mm_logger* g_logger = mm_logger_instance();
		//mm_logger_log_I(g_logger,"%s %d 1.",__FUNCTION__,__LINE__);
		////////////////////////////////////////////////////////////////////////////
		////mm_flake_context* flake_context = this->get_context();
		////CEGUI::System* _cegui_system_ptr = flake_context->d_cegui_system.d_system;
		////mm_flake_surface* flake_surface = flake_context->get_main_flake_surface();
		////Ogre::RenderWindow* render_window = flake_surface->get_render_window();
		////////////////////////////////////////////////////////////////////////////
		//int index = 0;
		////float d = 0;
		//char index_string[64] = {0};
		//mm_sprintf(index_string,"_%d", index);
		//////////////////////////////////////////////////////////////////////
		//CEGUI::GUIContext* _gui_context = surface->d_gui_context;
		////CEGUI::GUIContext* _gui_context = &_cegui_system_ptr->getDefaultGUIContext();
		////CEGUI::GUIContext* _gui_context = surface->d_gui_context;
		////flake_context->d_cegui_system.set_system_default_ttf_source("Miui-Bold.ttf");
		////flake_context->d_cegui_system.set_system_default_ttf_source("Miui-Regular.ttf");
		////flake_context->d_cegui_system.set_system_default_ttf_source("NotoSansSC-Regular.otf");
		//// here we create a default font.		
		//std::string system_font_12_name = "system_font_12";
		//system_font_12_name = system_font_12_name + index_string;
		//CEGUI::Font& _defaultFont = this->flake_context_reconfirm->d_cegui_system.create_system_font_by_size(surface, system_font_12_name.c_str(), 12.0f);
		CEGUI::GUIContext* _gui_context = surface->d_gui_context;
		_gui_context->getMouseCursor().setDefaultImage("TaharezLook/MouseArrow");
		//// The next thing we do is to set a default mouse cursor image.  This is
		//// not strictly essential, although it is nice to always have a visible
		//// cursor if a window or widget does not explicitly set one of its own.
		////
		//// The TaharezLook Imageset contains an Image named "MouseArrow" which is
		//// the ideal thing to have as a defult mouse cursor image.
		//_gui_context->getMouseCursor().setDefaultImage("TaharezLook/MouseArrow");

		//// load font and setup default if not loaded via scheme
		//// Set default font for the gui context
		//_gui_context->setDefaultFont(&_defaultFont);
		////////////////////////////////////////////////////////////////////////////
		this->bomber_reconfirm_funhui_1=funhui_diaoyong;


		CEGUI::WindowManager& _winMgr = CEGUI::WindowManager::getSingleton();
		//std::string root_window_name = "root_window";
		//root_window_name = root_window_name + index_string;
		//this->d_window = (CEGUI::Window*)_winMgr.createWindow("DefaultWindow", root_window_name.c_str());
		//CEGUI::Window* l_home_page_main = _winMgr.loadLayoutFromFile("l_home_page_main.layout");
		//CEGUI::Window* l_home_page_account = _winMgr.loadLayoutFromFile("l_home_page_account.layout");


		this->l_reconfirm = _winMgr.loadLayoutFromFile("l_reconfirm.layout");

		//CEGUI::Window* l_title = _winMgr.loadLayoutFromFile("l_title.layout");
		//l_title->addChild(l_home_page_account);
		//l_home_page_main->addChild(l_title);
		//w->addChild(l_home_page_main);
		//w->addChild(l_home_page_account);

		//this->d_window->addChild(this->l_reconfirm);

		//_gui_context->setRootWindow(this->d_window);

		_gui_context->setRootWindow(this->l_reconfirm);
		//////////////////////////////////////////////////////////////////////////
		_frameWindow = this->l_reconfirm->getChild("FrameWindow");
		l_Label_reconfirm = _frameWindow->getChild("Label_reconfirm");
		l_Button_yes = _frameWindow->getChild("Button_yes");
		l_Button_back = _frameWindow->getChild("Button_back");


		//l_Label_reconfirm ->setText(this->show_text);
		this->l_Button_yes_conn = l_Button_yes->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_reconfirm::on_handle_l_reconfirm_Button_yes_clicked, this));
		this->l_Button_back_conn = l_Button_back->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_reconfirm::on_handle_l_reconfirm_Button_back_clicked, this));


		this->l_reconfirm->setVisible(0);
	}

	void bomber_reconfirm::bomber_reconfirm_terminate( mm_flake_surface* surface )
	{
		struct mm_logger* g_logger = mm_logger_instance();
		CEGUI::WindowManager& _window_manager = CEGUI::WindowManager::getSingleton();

		this->l_Button_yes_conn->disconnect();
		this->l_Button_back_conn->disconnect();

		//this->d_window->removeChild(this->l_reconfirm);

		_window_manager.destroyWindow(this->l_reconfirm);

		//_window_manager.destroyWindow(this->d_window);

		mm_logger_log_I(g_logger,"mm_bomber::%s %d success.",__FUNCTION__,__LINE__);
	}

	//bool bomber_reconfirm::on_handle_l_ensure_exit_clicked(const CEGUI::EventArgs& args)
	//{
	//	mm_flake_context* flake_context = this->flake_context_reconfirm;
	//	flake_context->shutdown();

	//	return false;
	//}

	bool bomber_reconfirm::on_handle_l_reconfirm_Button_yes_clicked(const CEGUI::EventArgs& args)
	{
		mm_flake_context* flake_context = this->flake_context_reconfirm;
		(*(this->bomber_reconfirm_funhui_1))(this);
		this->l_reconfirm->setVisible(0);
		return false;
	}

	bool bomber_reconfirm::on_handle_l_reconfirm_Button_back_clicked(const CEGUI::EventArgs& args)
	{
		mm_flake_context* flake_context = this->flake_context_reconfirm;

		this->l_reconfirm->setVisible(0);
		return false;
	}


	//bool bomber_reconfirm::on_handle_l_reconfirm_Button_trolley_clicked(const CEGUI::EventArgs& args)
	//{
	//	mm_flake_context* flake_context = this->flake_context_reconfirm;
	//	flake_context->shutdown();

	//	return false;
	//}

	//bool bomber_reconfirm::on_handle_l_reconfirm_Button_cancel_clicked(const CEGUI::EventArgs& args)
	//{
	//	mm_flake_context* flake_context = this->flake_context_reconfirm;
	//	this->l_ensure->setVisible(1);
	//	this->l_reconfirm->setVisible(0);
	//	return false;
	//}


}
