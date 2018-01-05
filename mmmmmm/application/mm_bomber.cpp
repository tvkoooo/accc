#include "mm_bomber.h"

#include "core/mm_logger.h"

#include "dish/mm_file_context.h"
#include "dish/mm_lua_context.h"
#include "lua/lua_tinker.h"

#include "CEGUI/System.h"
#include "CEGUI/GUIContext.h"
#include "CEGUI/SchemeManager.h"
#include "CEGUI/WindowManager.h"
#include "CEGUI/widgets/DefaultWindow.h"

#include "CEGUI/XMLParserModules/TinyXML/XMLParserModule.h"

#include "mm_cegui_ogre_renderer/ImageCodec.h"
#include "mm_cegui_ogre_renderer/ResourceProvider.h"

#include "OgreCamera.h"
#include "OgreSceneNode.h"
#include "OgreEntity.h"
#include "OgreViewport.h"
#include "OgreMaterialManager.h"

#include "flake/mm_flake_context.h"
#include "flake/mm_flake_surface.h"

#include "net/mm_sockaddr.h"
#include "net/mm_mailbox.h"

//#include "openssl/rsa.h"
//#include "openssl/bn.h"
//#include "openssl/ossl_typ.h"
//
//#include "crypto/rsa/rsa_locl.h"

#include "tinyxml.h"


//////////////////////////////////////////////////////////////////////////

namespace mm
{
	//////////////////////////////////////////////////////////////////////////
	mm_flake_activity* mm_flake_activity_native_alloc()
	{
		return new mm_bomber;
	}
	void mm_flake_activity_native_dealloc(mm_flake_activity* v)
	{
		delete v;
	}
	//////////////////////////////////////////////////////////////////////////
	mm_bomber::mm_bomber()
		: d_scene_manager(NULL)
		, d_camera(NULL)

		,boy_x(0)
		,boy_y(0)
		,boy_z(0)

		, d_root_node(NULL)
		, d_light_node(NULL)
		, d_dir_light(NULL)

		, d_ogrehead_node_0(NULL)
		, d_ogrehead_mesh_0(NULL)

		, d_viewport(NULL)

		//, d_window(NULL)
		//, l_ensure(NULL)
	{
		//{
		//	int a = sizeof(struct mm_sockaddr);
		//	struct mm_sockaddr sa;
		//	mm_sockaddr_init(&sa);
		//	mm_sockaddr_assign(&sa,"::1",8555);
		//	mm_sockaddr_destroy(&sa);

		//	struct mm_mailbox mailbox;
		//	mm_mailbox_init(&mailbox);
		//	mm_mailbox_assign_native(&mailbox,"::1", 8555);
		//	mm_mailbox_set_length(&mailbox,4);
		//	mm_mailbox_fopen_socket(&mailbox);
		//	mm_mailbox_bind(&mailbox);
		//	mm_mailbox_listen(&mailbox);
		//	mm_mailbox_start(&mailbox);
		//	mm_mailbox_join(&mailbox);
		//	mm_mailbox_destroy(&mailbox);
		//}

	}

	mm_bomber::~mm_bomber()
	{

	}

	void mm_bomber::test_s_fuzhi( mm_flake_surface* surface )
	{
		this->bomber_home_main_u1.boy_x=this->boy_x;
		this->bomber_home_main_u1.boy_y=this->boy_y;
		this->bomber_home_main_u1.boy_z=this->boy_z;
		//this->bomber_home_main_u1.bomber_home_main_exit1=&on_handle_go_left_changed;

	}


	void mm_bomber::on_finish_launching()
	{
		
		struct mm_logger* g_logger = mm_logger_instance();
		mm_logger_log_I(g_logger,"%s %d 1.",__FUNCTION__,__LINE__);
		mm_flake_context* flake_context = this->get_context();
		mm_flake_surface* flake_surface = flake_context->get_main_flake_surface();
		//ogre::renderwindow* render_window = flake_surface->get_render_window();
		////////////////////////////////////////////////////////////////////////////
		flake_context->acquire_plugin_feature("Plugin_ParticleFX");
		flake_context->acquire_plugin_feature("Plugin_OctreeSceneManager");
		mm_logger_log_I(g_logger,"%s %d 2.",__FUNCTION__,__LINE__);
		////////////////////////////////////////////////////////////////////////
		// CEGUI relies on various systems being set-up, so this is what we do
		// here first.
		//
		// The first thing to do is load a CEGUI 'scheme' this is basically a file
		// that groups all the required resources and definitions for a particular
		// skin so they can be loaded / initialised easily
		//
		// So, we use the SchemeManager singleton to load in a scheme that loads the
		// imagery and registers widgets for the TaharezLook skin.  This scheme also
		// loads in a font that gets used as the system default.
		CEGUI::SchemeManager::getSingleton().createFromFile("TaharezLook.scheme");
		mm_logger_log_I(g_logger,"%s %d 3.",__FUNCTION__,__LINE__);
		///////////////////////////////////////////////////////////////////



		Ogre::ResourceGroupManager& _resource_group_mgr = Ogre::ResourceGroupManager::getSingleton();
		///////////////////////////////////////////////////////////////////
		_resource_group_mgr.addResourceLocation(".", "mm_file_system", Ogre::ResourceGroupManager::DEFAULT_RESOURCE_GROUP_NAME);
		_resource_group_mgr.addResourceLocation("media/materials/scripts", "mm_file_system", Ogre::ResourceGroupManager::DEFAULT_RESOURCE_GROUP_NAME);
		_resource_group_mgr.addResourceLocation("media/materials/textures", "mm_file_system", Ogre::ResourceGroupManager::DEFAULT_RESOURCE_GROUP_NAME);



		// location shader language.
		const std::string& _language_directory = flake_context->get_language_directory();
		_resource_group_mgr.addResourceLocation("media/materials/programs/" + _language_directory, "mm_file_system", Ogre::ResourceGroupManager::DEFAULT_RESOURCE_GROUP_NAME);
		_resource_group_mgr.initialiseResourceGroup(Ogre::ResourceGroupManager::DEFAULT_RESOURCE_GROUP_NAME);
		mm_logger_log_I(g_logger,"%s %d 4.",__FUNCTION__,__LINE__);
		////启动  入口//////////////////////////////////////////////////////
		this->test_s_launching(flake_surface);
		this->bomber_home_main_u1.mm_flake_context_assignment(flake_context);
		this->bomber_home_main_u1.bomber_home_main_launching(flake_surface);
		//this->bomber_reconfirm_r1.bomber_reconfirm_launching(flake_surface);

		//////////////////////////////////////////////////////////////////////////
		//double f = 0;
		//for (int i = 0;i< 99999999;++i)
		//{
		//	f = f + 1.01f;
		//}
		//mm_logger_log_I(g_logger,"%s %d %lf.",__FUNCTION__,__LINE__,f);
		//{
		//	float p3x = 80838.0f;
		//	float p2y = -2499.0f;
		//	double v321 = p3x * p2y;
		//	mm_logger_log_I(g_logger,"%s %d %lf.",__FUNCTION__,__LINE__,v321);
		//}
		//////////////////////////////////////////////////////////////////////////
	}

	void mm_bomber::on_before_terminate()
	{
		//////////////////////////////////////////////////////////////////////
		mm_flake_context* flake_context = this->get_context();
		mm_flake_surface* flake_surface = flake_context->get_main_flake_surface();
		/////结束  出口//////////////////////////////////////////////////////////////
		this->test_s_terminate(flake_surface);
		//this->bomber_reconfirm_r1.bomber_reconfirm_terminate(flake_surface);
		this->bomber_home_main_u1.bomber_home_main_terminate(flake_surface);

		////////////////////////////////////////////////////////////////////////
		// destroy all windows manual.
		CEGUI::WindowManager& _winMgr = CEGUI::WindowManager::getSingleton();
		_winMgr.destroyAllWindows();
		// clear dead pool immediately,make sure malloc can free.
		_winMgr.cleanDeadPool();
		//////////////////////////////////////////////////////////////////////////
		Ogre::ResourceGroupManager& _resource_group_mgr = Ogre::ResourceGroupManager::getSingleton();
		_resource_group_mgr.removeResourceLocation(".", Ogre::ResourceGroupManager::DEFAULT_RESOURCE_GROUP_NAME);
		_resource_group_mgr.removeResourceLocation("media/materials/scripts", Ogre::ResourceGroupManager::DEFAULT_RESOURCE_GROUP_NAME);
		_resource_group_mgr.removeResourceLocation("media/materials/textures", Ogre::ResourceGroupManager::DEFAULT_RESOURCE_GROUP_NAME);

		// location shader language.
		const std::string& _language_directory = flake_context->get_language_directory();
		_resource_group_mgr.removeResourceLocation("media/materials/programs/" + _language_directory, Ogre::ResourceGroupManager::DEFAULT_RESOURCE_GROUP_NAME);
	}

	void mm_bomber::on_start()
	{

	}
	void mm_bomber::on_interrupt()
	{

	}
	void mm_bomber::on_shutdown()
	{

	}
	void mm_bomber::on_join()
	{

	}

	void mm_bomber::test_s_launching( mm_flake_surface* surface )
	{
		float d = 0;
		
		//////////////////////////////////////////////////////////////////////////
		mm_flake_context* flake_context = this->get_context();
		//////////////////////////////////////////////////////////////////////////
		this->d_window_size_changed_conn = surface->d_event_set.subscribe_event(mm_flake_surface::event_window_size_changed, &mm_bomber::on_handle_window_size_changed, this);
		//////////////////////////////////////////////////////////////////////////
		Ogre::Root* _ogre_root = flake_context->d_ogre_system.get_ogre_root();

		// Create the scene manager
		this->d_scene_manager = _ogre_root->createSceneManager();
		Ogre::RTShader::ShaderGenerator::getSingletonPtr()->addSceneManager(this->d_scene_manager);
		// Create and initialise the camera
		this->d_camera = d_scene_manager->createCamera("main_camera");
		this->d_camera->setPosition(Ogre::Vector3(0,0,400));
		this->d_camera->lookAt(Ogre::Vector3(0,0,-300));
		this->d_camera->setNearClipDistance(1.0f);
		this->d_camera->setFarClipDistance(100000.0f);

		Ogre::Vector3 lightdir(0.55f, -0.3f, -0.75f);
		lightdir.normalise();
		this->d_root_node = this->d_scene_manager->getRootSceneNode();
		this->d_light_node = this->d_root_node->createChildSceneNode();
		this->d_dir_light = this->d_scene_manager->createLight();
		this->d_dir_light->setType(Ogre::Light::LT_DIRECTIONAL);
		this->d_dir_light->setDirection(lightdir);
		this->d_dir_light->setDiffuseColour(Ogre::ColourValue::White);
		this->d_dir_light->setSpecularColour(Ogre::ColourValue(0.4f, 0.4f, 0.4f));
		this->d_light_node->attachObject(this->d_dir_light);

		this->d_scene_manager->setAmbientLight(Ogre::ColourValue(1.0f, 1.0f, 1.0f));		
		/////////////////////////////////////////////////////////////////////
		this->d_ogrehead_node_0 = this->d_root_node->createChildSceneNode();
		this->d_ogrehead_mesh_0 = this->d_scene_manager->createEntity("ogrehead_0", "media/physics/ogrehead.mesh");

		this->d_ogrehead_node_0->attachObject(this->d_ogrehead_mesh_0);
		this->d_ogrehead_node_0->yaw(Ogre::Radian(d));

		this->d_ogrehead_node_0->setPosition(Ogre::Vector3(this->boy_x,this->boy_y,this->boy_z));

		this->d_viewport = surface->d_render_window->addViewport(this->d_camera);
		this->d_camera->setAspectRatio(Ogre::Real(this->d_viewport->getActualWidth()) / Ogre::Real(this->d_viewport->getActualHeight()));

		//this->bomber_home_main_u1.l_ensure->setVisible(1);
		//this->bomber_home_main_u1.l_home_main->setVisible(0);
		//this->bomber_reconfirm_r1.l_reconfirm->setVisible(0);

		//this->bomber_home_main_u1.bomber_home_main_exit1=&mm_bomber::on_handle_go_left_changed;


	}
	void mm_bomber::test_s_terminate( mm_flake_surface* surface )
	{
		struct mm_logger* g_logger = mm_logger_instance();
		mm_flake_context* flake_context = this->get_context();
		Ogre::Root* _ogre_root = flake_context->d_ogre_system.get_ogre_root();
		//////////////////////////////////////////////////////////////////////////
		surface->d_event_set.unsubscribe_event(mm_flake_surface::event_window_size_changed, this->d_window_size_changed_conn);
		//////////////////////////////////////////////////////////////////////////
		surface->d_render_window->removeViewport(this->d_viewport->getZOrder());
		this->d_scene_manager->destroyEntity(this->d_ogrehead_mesh_0);
		this->d_scene_manager->destroySceneNode(this->d_ogrehead_node_0);

		this->d_scene_manager->destroyLight(this->d_dir_light);
		this->d_scene_manager->destroySceneNode(this->d_light_node);
		this->d_scene_manager->destroyCamera(this->d_camera);

		Ogre::RTShader::ShaderGenerator::getSingletonPtr()->removeSceneManager(this->d_scene_manager);
		_ogre_root->destroySceneManager(this->d_scene_manager);
		mm_logger_log_I(g_logger,"mm_bomber::%s %d success.",__FUNCTION__,__LINE__);
	}

	//void mm_bomber::test_l_launching( mm_flake_surface* surface )
	//{
	//	struct mm_logger* g_logger = mm_logger_instance();
	//	mm_logger_log_I(g_logger,"%s %d 1.",__FUNCTION__,__LINE__);
	//	//////////////////////////////////////////////////////////////////////////
	//	mm_flake_context* flake_context = this->get_context();
	//	//CEGUI::System* _cegui_system_ptr = flake_context->d_cegui_system.d_system;
	//	//mm_flake_surface* flake_surface = flake_context->get_main_flake_surface();
	//	//Ogre::RenderWindow* render_window = flake_surface->get_render_window();
	//	//////////////////////////////////////////////////////////////////////////
	//	int index = 0;
	//	//float d = 0;
	//	char index_string[64] = {0};
	//	mm_sprintf(index_string,"_%d", index);
	//	////////////////////////////////////////////////////////////////////
	//	CEGUI::GUIContext* _gui_context = surface->d_gui_context;
	//	//CEGUI::GUIContext* _gui_context = &_cegui_system_ptr->getDefaultGUIContext();
	//	//CEGUI::GUIContext* _gui_context = surface->d_gui_context;
	//	//flake_context->d_cegui_system.set_system_default_ttf_source("Miui-Bold.ttf");
	//	//flake_context->d_cegui_system.set_system_default_ttf_source("Miui-Regular.ttf");
	//	//flake_context->d_cegui_system.set_system_default_ttf_source("NotoSansSC-Regular.otf");
	//	// here we create a default font.		
	//	std::string system_font_12_name = "system_font_12";
	//	system_font_12_name = system_font_12_name + index_string;
	//	CEGUI::Font& _defaultFont = flake_context->d_cegui_system.create_system_font_by_size(surface, system_font_12_name.c_str(), 12.0f);
	//	// The next thing we do is to set a default mouse cursor image.  This is
	//	// not strictly essential, although it is nice to always have a visible
	//	// cursor if a window or widget does not explicitly set one of its own.
	//	//
	//	// The TaharezLook Imageset contains an Image named "MouseArrow" which is
	//	// the ideal thing to have as a defult mouse cursor image.
	//	_gui_context->getMouseCursor().setDefaultImage("TaharezLook/MouseArrow");
	//	// load font and setup default if not loaded via scheme
	//	// Set default font for the gui context
	//	_gui_context->setDefaultFont(&_defaultFont);
	//	////////////////////////////////////////////////////////////////////////////
	//	CEGUI::WindowManager& _winMgr = CEGUI::WindowManager::getSingleton();
	//	std::string root_window_name = "root_window";
	//	root_window_name = root_window_name + index_string;
	//	this->d_window = (CEGUI::Window*)_winMgr.createWindow("DefaultWindow", root_window_name.c_str());
	//	//CEGUI::Window* l_home_page_main = _winMgr.loadLayoutFromFile("l_home_page_main.layout");
	//	//CEGUI::Window* l_home_page_account = _winMgr.loadLayoutFromFile("l_home_page_account.layout");
	//	this->l_ensure = _winMgr.loadLayoutFromFile("l_ensure.layout");
	//	//CEGUI::Window* l_title = _winMgr.loadLayoutFromFile("l_title.layout");
	//	//l_title->addChild(l_home_page_account);
	//	//l_home_page_main->addChild(l_title);
	//	//w->addChild(l_home_page_main);
	//	//w->addChild(l_home_page_account);
	//	this->d_window->addChild(this->l_ensure);
	//	_gui_context->setRootWindow(this->d_window);
	//	//////////////////////////////////////////////////////////////////////////
	//	CEGUI::Window* _frameWindow = this->l_ensure->getChild("FrameWindow");
	//	CEGUI::Window* Button_ensure = _frameWindow->getChild("Button_ensure");
	//	CEGUI::Window* Button_cancel = _frameWindow->getChild("Button_cancel");
	//	this->d_ensure_conn = Button_ensure->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&mm_bomber::on_handle_exit_ensure_clicked, this));
	//	this->d_cancel_conn = Button_cancel->subscribeEvent(CEGUI::Window::EventMouseClick,  CEGUI::Event::Subscriber(&mm_bomber::on_handle_open_cancel_clicked, this));
	//}

	//void mm_bomber::test_l_terminate( mm_flake_surface* surface )
	//{
	//	struct mm_logger* g_logger = mm_logger_instance();
	//	CEGUI::WindowManager& _window_manager = CEGUI::WindowManager::getSingleton();
	//	this->d_ensure_conn->disconnect();
	//	this->d_window->removeChild(this->l_ensure);
	//	_window_manager.destroyWindow(this->l_ensure);
	//	_window_manager.destroyWindow(this->d_window);
	//	mm_logger_log_I(g_logger,"mm_bomber::%s %d success.",__FUNCTION__,__LINE__);
	//}

	//bool mm_bomber::on_handle_exit_ensure_clicked(const CEGUI::EventArgs& args)
	//{
	//	mm_flake_context* flake_context = this->get_context();
	//	flake_context->shutdown();
	//	return false;
	//}

	//bool mm_bomber::on_handle_open_cancel_clicked(const CEGUI::EventArgs& args)
	//{
	//	mm_flake_context* flake_context = this->get_context();
	//	flake_context->shutdown();

	//	//lllk.assignment_mm_flake_context(flake_context);
	//	//lllk.on_finish_launching();


	//	return false;
	//}

	bool mm_bomber::on_handle_window_size_changed(const mm_event_args& args)
	{
		int _actual_w = this->d_viewport->getActualWidth();
		int _actual_h = this->d_viewport->getActualHeight();
		Ogre::Real _aspect_ratio = Ogre::Real(_actual_w) / Ogre::Real(_actual_h);
		this->d_camera->setAspectRatio(_aspect_ratio);
		return false;
	}


	static void on_handle_go_left_changed(bomber_home_main * p)
	{
		//p->boy_x-=10;

		//p->d_ogrehead_node_0->setPosition(Ogre::Vector3(p->boy_x,p->boy_y,p->boy_z));
		/*return false;*/
	}

}
