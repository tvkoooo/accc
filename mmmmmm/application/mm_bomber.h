#ifndef __mm_bomber_h__
#define __mm_bomber_h__

#include "core/mm_core.h"

#include "flake/mm_flake_activity.h"
#include "flake/mm_flake_surface.h"

#include "dish/mm_event.h"

#include "mm_bomber_home_main.h"

namespace mm
{
	extern mm_flake_activity* mm_flake_activity_native_alloc();
	extern void mm_flake_activity_native_dealloc(mm_flake_activity* v);

	class mm_bomber : public mm_flake_activity
	{
		public:
		 bomber_home_main bomber_home_main_u1;


	public:
		Ogre::SceneManager* d_scene_manager;
		Ogre::Camera* d_camera;

		Ogre::SceneNode* d_root_node;
		Ogre::SceneNode* d_light_node;
		Ogre::Light* d_dir_light;

		Ogre::SceneNode* d_ogrehead_node_0;
		Ogre::Entity* d_ogrehead_mesh_0;

		Ogre::Viewport* d_viewport;

		mm_event_handler d_window_size_changed_conn;
	//public:
	//	CEGUI::Window* d_window;
	//	CEGUI::Window* l_ensure;
	//	CEGUI::Event::Connection d_ensure_conn;
	//	CEGUI::Event::Connection d_cancel_conn;

	public:
		void test_s_terminate( mm_flake_surface* surface );
		void test_s_launching( mm_flake_surface* surface );

	//	void test_l_terminate( mm_flake_surface* surface );
	//	void test_l_launching( mm_flake_surface* surface );
	public:
		mm_bomber();
		virtual ~mm_bomber();
	public:
		virtual void on_finish_launching();
		virtual void on_before_terminate();
	public:
		virtual void on_start();
		virtual void on_interrupt();
		virtual void on_shutdown();
		virtual void on_join();
	//public:
	//	bool on_handle_exit_ensure_clicked(const CEGUI::EventArgs& args);
	//	bool on_handle_open_cancel_clicked(const CEGUI::EventArgs& args);

		bool on_handle_window_size_changed(const mm_event_args& args);

	//public:
	//	bomber_home_main lllk;
	};
}

#endif//__mm_bomber_h__