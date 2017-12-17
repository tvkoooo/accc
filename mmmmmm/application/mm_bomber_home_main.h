

#ifndef _mm_bomber_home_main_
#define _mm_bomber_home_main_

#include "core/mm_core.h"

#include "flake/mm_flake_activity.h"
#include "flake/mm_flake_surface.h"

#include "dish/mm_event.h"

namespace mm
{
	class bomber_home_main 
	{
	public:
		bomber_home_main();
		virtual ~bomber_home_main();

	public:
		CEGUI::Window* d_window;

		CEGUI::Window* l_ensure;
		CEGUI::Event::Connection l_ensure_ensure_conn;
		CEGUI::Event::Connection l_ensure_cancel_conn;

		CEGUI::Window* l_home_main;
		CEGUI::Event::Connection l_home_main_trolley_conn;
		CEGUI::Event::Connection l_home_main_cancel_conn;


	public:
		void bomber_home_main_terminate( mm_flake_surface* surface );
		void bomber_home_main_launching( mm_flake_surface* surface );

	public:
		mm_flake_context* flake_context_home_main;
	public:
		void mm_flake_context_assignment(mm_flake_context* p_flake_context);


	public:
		bool on_handle_l_ensure_ensure_clicked(const CEGUI::EventArgs& args);
		bool on_handle_l_ensure_cancel_clicked(const CEGUI::EventArgs& args);

		bool on_handle_l_home_main_Button_trolley_clicked(const CEGUI::EventArgs& args);
		bool on_handle_l_home_main_Button_cancel_clicked(const CEGUI::EventArgs& args);

		bool on_handle_window_size_changed(const mm_event_args& args);
	};


}

#endif//_mm_bomber_home_main_