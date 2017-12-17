#include "mm_bomber_home_main.h"


//#include "core/mm_logger.h"
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
		: d_window(NULL)
		, l_ensure(NULL)
		, l_home_main(NULL)
	{

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

			//CEGUI::Window* l_title = _winMgr.loadLayoutFromFile("l_title.layout");
			//l_title->addChild(l_home_page_account);
			//l_home_page_main->addChild(l_title);
			//w->addChild(l_home_page_main);
			//w->addChild(l_home_page_account);

			this->d_window->addChild(this->l_ensure);
			this->d_window->addChild(this->l_home_main);

			_gui_context->setRootWindow(this->d_window);

			//////////////////////////////////////////////////////////////////////////
			CEGUI::Window* _frameWindow = this->l_ensure->getChild("FrameWindow");
			CEGUI::Window* l_ensure_Button_ensure = _frameWindow->getChild("Button_ensure");
			CEGUI::Window* l_ensure_Button_cancel = _frameWindow->getChild("Button_cancel");
			this->l_ensure_ensure_conn = l_ensure_Button_ensure->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_home_main::on_handle_l_ensure_ensure_clicked, this));
			this->l_ensure_cancel_conn = l_ensure_Button_cancel->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_home_main::on_handle_l_ensure_cancel_clicked, this));
			//////////////////////////////////////////////////////////////////////////
			CEGUI::Window* _StaticImage = this->l_home_main->getChild("StaticImage");
			CEGUI::Window* _l_trolley = this->l_home_main->getChild("l_trolley");
			CEGUI::Window* l_home_main_Button_trolley = _l_trolley->getChild("Button_trolley");
			CEGUI::Window* l_home_main_Button_cancel = _l_trolley->getChild("Button_cancel");
			this->l_home_main_trolley_conn = l_home_main_Button_trolley->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_home_main::on_handle_l_home_main_Button_trolley_clicked, this));
			this->l_home_main_cancel_conn = l_home_main_Button_cancel->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&bomber_home_main::on_handle_l_home_main_Button_cancel_clicked, this));

			this->l_ensure->setVisible(1);
			this->l_home_main->setVisible(0);
		}

	void bomber_home_main::bomber_home_main_terminate( mm_flake_surface* surface )
	{
		struct mm_logger* g_logger = mm_logger_instance();
		CEGUI::WindowManager& _window_manager = CEGUI::WindowManager::getSingleton();

		this->l_ensure_ensure_conn->disconnect();
		this->l_ensure_cancel_conn->disconnect();

		this->d_window->removeChild(this->l_ensure);
		this->d_window->removeChild(this->l_home_main);

		_window_manager.destroyWindow(this->l_ensure);
		_window_manager.destroyWindow(this->l_home_main);

		_window_manager.destroyWindow(this->d_window);

		mm_logger_log_I(g_logger,"mm_bomber::%s %d success.",__FUNCTION__,__LINE__);
	}

	bool bomber_home_main::on_handle_l_ensure_ensure_clicked(const CEGUI::EventArgs& args)
	{
		mm_flake_context* flake_context = this->flake_context_home_main;
		flake_context->shutdown();

		return false;
	}

	bool bomber_home_main::on_handle_l_ensure_cancel_clicked(const CEGUI::EventArgs& args)
	{
		mm_flake_context* flake_context = this->flake_context_home_main;
		this->l_ensure->setVisible(0);
		this->l_home_main->setVisible(1);

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
}
