

#ifndef _mm_bomber_reconfirm_
#define _mm_bomber_reconfirm_

#include "core/mm_core.h"

#include "flake/mm_flake_activity.h"
#include "flake/mm_flake_surface.h"

#include "dish/mm_event.h"
#include <fstream>

namespace mm
{
	typedef void (*bomber_reconfirm_huidiao_type) (class bomber_reconfirm* obj);
	class bomber_reconfirm 
	{
	public:
		bomber_reconfirm();
		virtual ~bomber_reconfirm();
	public:
		bomber_reconfirm_huidiao_type bomber_reconfirm_funhui_1;
	public:
		void  bomber_reconfirm_assignment(bomber_reconfirm_huidiao_type fun);

	public:
		//CEGUI::Window* d_window;

		//CEGUI::Window* l_ensure;
		//CEGUI::Event::Connection l_ensure_Label_table_conn;
		//CEGUI::Event::Connection l_ensure_Button_exit_conn;
		//CEGUI::Event::Connection l_ensure_Button_login_conn;
		//CEGUI::Event::Connection l_ensure_Button_apply_conn;
		//CEGUI::Event::Connection l_ensure_Editbox_username_conn;
		//CEGUI::Event::Connection l_ensure_Editbox_password_conn;


		CEGUI::Window* l_reconfirm;
		CEGUI::Event::Connection l_Label_reconfirm_conn;
		CEGUI::Event::Connection l_Button_yes_conn;
		CEGUI::Event::Connection l_Button_back_conn;


	public:
		void bomber_reconfirm_terminate( mm_flake_surface* surface );
		void bomber_reconfirm_launching( mm_flake_surface* surface );

	public:
		mm_flake_context* flake_context_reconfirm;
	public:
		void mm_flake_context_assignment(mm_flake_context* p_flake_context);


	public:
		//bool on_handle_l_ensure_exit_clicked(const CEGUI::EventArgs& args);
		//bool on_handle_l_ensure_login_clicked(const CEGUI::EventArgs& args);
		//bool on_handle_l_ensure_apply_clicked(const CEGUI::EventArgs& args);

		bool on_handle_l_reconfirm_Button_yes_clicked(const CEGUI::EventArgs& args);
		bool on_handle_l_reconfirm_Button_back_clicked(const CEGUI::EventArgs& args);

		bool on_handle_window_size_changed(const mm_event_args& args);

	public:


		CEGUI::Window* _frameWindow ;;//= this->l_ensure->getChild("FrameWindow");
		//CEGUI::Window* l_ensure_Label_table ;;//= _frameWindow->getChild("Label_desc");
		//CEGUI::Window* l_ensure_Button_exit ;;//= _frameWindow->getChild("Button_ensure");
		//CEGUI::Window* l_ensure_Button_login ;;//= _frameWindow->getChild("Button_cancel");
		//CEGUI::Window* l_ensure_Button_apply ;;//= _frameWindow->getChild("Editbox_username");
		//CEGUI::Window* l_ensure_Editbox_username ;;//= _frameWindow->getChild("Editbox_password");
		//CEGUI::Window* l_ensure_Editbox_password ;;//= _frameWindow->getChild("Editbox_password");

		CEGUI::Window* l_Label_reconfirm ;;//= _frameWindow->getChild("Label_desc");
		CEGUI::Window* l_Button_yes ;//= _frameWindow->getChild("Editbox_password");
		CEGUI::Window* l_Button_back ;//= _frameWindow->getChild("Editbox_password");

	};


}

#endif//_mm_bomber_reconfirm_