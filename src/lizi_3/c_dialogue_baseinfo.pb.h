// Generated by the protocol buffer compiler.  DO NOT EDIT!
// source: c_dialogue_baseinfo.proto

#ifndef PROTOBUF_c_5fdialogue_5fbaseinfo_2eproto__INCLUDED
#define PROTOBUF_c_5fdialogue_5fbaseinfo_2eproto__INCLUDED

#include <string>

#include <google/protobuf/stubs/common.h>

#if GOOGLE_PROTOBUF_VERSION < 3003000
#error This file was generated by a newer version of protoc which is
#error incompatible with your Protocol Buffer headers.  Please update
#error your headers.
#endif
#if 3003000 < GOOGLE_PROTOBUF_MIN_PROTOC_VERSION
#error This file was generated by an older version of protoc which is
#error incompatible with your Protocol Buffer headers.  Please
#error regenerate this file with a newer version of protoc.
#endif

#include <google/protobuf/io/coded_stream.h>
#include <google/protobuf/arena.h>
#include <google/protobuf/arenastring.h>
#include <google/protobuf/generated_message_table_driven.h>
#include <google/protobuf/generated_message_util.h>
#include <google/protobuf/metadata_lite.h>
#include <google/protobuf/message_lite.h>
#include <google/protobuf/repeated_field.h>  // IWYU pragma: export
#include <google/protobuf/extension_set.h>  // IWYU pragma: export
// @@protoc_insertion_point(includes)
namespace c_dialogue_baseinfo {
class dialogue_baseinfo;
class dialogue_baseinfoDefaultTypeInternal;
extern dialogue_baseinfoDefaultTypeInternal _dialogue_baseinfo_default_instance_;
}  // namespace c_dialogue_baseinfo

namespace c_dialogue_baseinfo {

namespace protobuf_c_5fdialogue_5fbaseinfo_2eproto {
// Internal implementation detail -- do not call these.
struct TableStruct {
  static const ::google::protobuf::internal::ParseTableField entries[];
  static const ::google::protobuf::internal::AuxillaryParseTableField aux[];
  static const ::google::protobuf::internal::ParseTable schema[];
  static const ::google::protobuf::uint32 offsets[];
  static void InitDefaultsImpl();
  static void Shutdown();
};
void AddDescriptors();
void InitDefaults();
}  // namespace protobuf_c_5fdialogue_5fbaseinfo_2eproto

// ===================================================================

class dialogue_baseinfo : public ::google::protobuf::MessageLite /* @@protoc_insertion_point(class_definition:c_dialogue_baseinfo.dialogue_baseinfo) */ {
 public:
  dialogue_baseinfo();
  virtual ~dialogue_baseinfo();

  dialogue_baseinfo(const dialogue_baseinfo& from);

  inline dialogue_baseinfo& operator=(const dialogue_baseinfo& from) {
    CopyFrom(from);
    return *this;
  }

  inline const ::std::string& unknown_fields() const {
    return _internal_metadata_.unknown_fields();
  }

  inline ::std::string* mutable_unknown_fields() {
    return _internal_metadata_.mutable_unknown_fields();
  }

  static const dialogue_baseinfo& default_instance();

  static inline const dialogue_baseinfo* internal_default_instance() {
    return reinterpret_cast<const dialogue_baseinfo*>(
               &_dialogue_baseinfo_default_instance_);
  }
  static PROTOBUF_CONSTEXPR int const kIndexInFileMessages =
    0;

  void Swap(dialogue_baseinfo* other);

  // implements Message ----------------------------------------------

  inline dialogue_baseinfo* New() const PROTOBUF_FINAL { return New(NULL); }

  dialogue_baseinfo* New(::google::protobuf::Arena* arena) const PROTOBUF_FINAL;
  void CheckTypeAndMergeFrom(const ::google::protobuf::MessageLite& from)
    PROTOBUF_FINAL;
  void CopyFrom(const dialogue_baseinfo& from);
  void MergeFrom(const dialogue_baseinfo& from);
  void Clear() PROTOBUF_FINAL;
  bool IsInitialized() const PROTOBUF_FINAL;

  size_t ByteSizeLong() const PROTOBUF_FINAL;
  bool MergePartialFromCodedStream(
      ::google::protobuf::io::CodedInputStream* input) PROTOBUF_FINAL;
  void SerializeWithCachedSizes(
      ::google::protobuf::io::CodedOutputStream* output) const PROTOBUF_FINAL;
  void DiscardUnknownFields();
  int GetCachedSize() const PROTOBUF_FINAL { return _cached_size_; }
  private:
  void SharedCtor();
  void SharedDtor();
  void SetCachedSize(int size) const;
  void InternalSwap(dialogue_baseinfo* other);
  private:
  inline ::google::protobuf::Arena* GetArenaNoVirtual() const {
    return NULL;
  }
  inline void* MaybeArenaPtr() const {
    return NULL;
  }
  public:

  ::std::string GetTypeName() const PROTOBUF_FINAL;

  // nested types ----------------------------------------------------

  // accessors -------------------------------------------------------

  // optional string error_desc = 3 [default = ""];
  bool has_error_desc() const;
  void clear_error_desc();
  static const int kErrorDescFieldNumber = 3;
  const ::std::string& error_desc() const;
  void set_error_desc(const ::std::string& value);
  #if LANG_CXX11
  void set_error_desc(::std::string&& value);
  #endif
  void set_error_desc(const char* value);
  void set_error_desc(const char* value, size_t size);
  ::std::string* mutable_error_desc();
  ::std::string* release_error_desc();
  void set_allocated_error_desc(::std::string* error_desc);

  // optional string user_nick = 5 [default = ""];
  bool has_user_nick() const;
  void clear_user_nick();
  static const int kUserNickFieldNumber = 5;
  const ::std::string& user_nick() const;
  void set_user_nick(const ::std::string& value);
  #if LANG_CXX11
  void set_user_nick(::std::string&& value);
  #endif
  void set_user_nick(const char* value);
  void set_user_nick(const char* value, size_t size);
  ::std::string* mutable_user_nick();
  ::std::string* release_user_nick();
  void set_allocated_user_nick(::std::string* user_nick);

  // optional string user_password = 6 [default = ""];
  bool has_user_password() const;
  void clear_user_password();
  static const int kUserPasswordFieldNumber = 6;
  const ::std::string& user_password() const;
  void set_user_password(const ::std::string& value);
  #if LANG_CXX11
  void set_user_password(::std::string&& value);
  #endif
  void set_user_password(const char* value);
  void set_user_password(const char* value, size_t size);
  ::std::string* mutable_user_password();
  ::std::string* release_user_password();
  void set_allocated_user_password(::std::string* user_password);

  // optional string to_user_nick = 8 [default = ""];
  bool has_to_user_nick() const;
  void clear_to_user_nick();
  static const int kToUserNickFieldNumber = 8;
  const ::std::string& to_user_nick() const;
  void set_to_user_nick(const ::std::string& value);
  #if LANG_CXX11
  void set_to_user_nick(::std::string&& value);
  #endif
  void set_to_user_nick(const char* value);
  void set_to_user_nick(const char* value, size_t size);
  ::std::string* mutable_to_user_nick();
  ::std::string* release_to_user_nick();
  void set_allocated_to_user_nick(::std::string* to_user_nick);

  // optional string talking = 9 [default = ""];
  bool has_talking() const;
  void clear_talking();
  static const int kTalkingFieldNumber = 9;
  const ::std::string& talking() const;
  void set_talking(const ::std::string& value);
  #if LANG_CXX11
  void set_talking(::std::string&& value);
  #endif
  void set_talking(const char* value);
  void set_talking(const char* value, size_t size);
  ::std::string* mutable_talking();
  ::std::string* release_talking();
  void set_allocated_talking(::std::string* talking);

  // optional string system_desc = 11 [default = ""];
  bool has_system_desc() const;
  void clear_system_desc();
  static const int kSystemDescFieldNumber = 11;
  const ::std::string& system_desc() const;
  void set_system_desc(const ::std::string& value);
  #if LANG_CXX11
  void set_system_desc(::std::string&& value);
  #endif
  void set_system_desc(const char* value);
  void set_system_desc(const char* value, size_t size);
  ::std::string* mutable_system_desc();
  ::std::string* release_system_desc();
  void set_allocated_system_desc(::std::string* system_desc);

  // optional uint32 enum_msg = 1;
  bool has_enum_msg() const;
  void clear_enum_msg();
  static const int kEnumMsgFieldNumber = 1;
  ::google::protobuf::uint32 enum_msg() const;
  void set_enum_msg(::google::protobuf::uint32 value);

  // optional uint32 error_state = 2 [default = 0];
  bool has_error_state() const;
  void clear_error_state();
  static const int kErrorStateFieldNumber = 2;
  ::google::protobuf::uint32 error_state() const;
  void set_error_state(::google::protobuf::uint32 value);

  // optional uint64 user_id = 4 [default = 0];
  bool has_user_id() const;
  void clear_user_id();
  static const int kUserIdFieldNumber = 4;
  ::google::protobuf::uint64 user_id() const;
  void set_user_id(::google::protobuf::uint64 value);

  // optional uint64 to_user_id = 7 [default = 0];
  bool has_to_user_id() const;
  void clear_to_user_id();
  static const int kToUserIdFieldNumber = 7;
  ::google::protobuf::uint64 to_user_id() const;
  void set_to_user_id(::google::protobuf::uint64 value);

  // optional uint32 system_state = 10 [default = 0];
  bool has_system_state() const;
  void clear_system_state();
  static const int kSystemStateFieldNumber = 10;
  ::google::protobuf::uint32 system_state() const;
  void set_system_state(::google::protobuf::uint32 value);

  // @@protoc_insertion_point(class_scope:c_dialogue_baseinfo.dialogue_baseinfo)
 private:
  void set_has_enum_msg();
  void clear_has_enum_msg();
  void set_has_error_state();
  void clear_has_error_state();
  void set_has_error_desc();
  void clear_has_error_desc();
  void set_has_user_id();
  void clear_has_user_id();
  void set_has_user_nick();
  void clear_has_user_nick();
  void set_has_user_password();
  void clear_has_user_password();
  void set_has_to_user_id();
  void clear_has_to_user_id();
  void set_has_to_user_nick();
  void clear_has_to_user_nick();
  void set_has_talking();
  void clear_has_talking();
  void set_has_system_state();
  void clear_has_system_state();
  void set_has_system_desc();
  void clear_has_system_desc();

  ::google::protobuf::internal::InternalMetadataWithArenaLite _internal_metadata_;
  ::google::protobuf::internal::HasBits<1> _has_bits_;
  mutable int _cached_size_;
  ::google::protobuf::internal::ArenaStringPtr error_desc_;
  ::google::protobuf::internal::ArenaStringPtr user_nick_;
  ::google::protobuf::internal::ArenaStringPtr user_password_;
  ::google::protobuf::internal::ArenaStringPtr to_user_nick_;
  ::google::protobuf::internal::ArenaStringPtr talking_;
  ::google::protobuf::internal::ArenaStringPtr system_desc_;
  ::google::protobuf::uint32 enum_msg_;
  ::google::protobuf::uint32 error_state_;
  ::google::protobuf::uint64 user_id_;
  ::google::protobuf::uint64 to_user_id_;
  ::google::protobuf::uint32 system_state_;
  friend struct protobuf_c_5fdialogue_5fbaseinfo_2eproto::TableStruct;
};
// ===================================================================


// ===================================================================

#if !PROTOBUF_INLINE_NOT_IN_HEADERS
// dialogue_baseinfo

// optional uint32 enum_msg = 1;
inline bool dialogue_baseinfo::has_enum_msg() const {
  return (_has_bits_[0] & 0x00000040u) != 0;
}
inline void dialogue_baseinfo::set_has_enum_msg() {
  _has_bits_[0] |= 0x00000040u;
}
inline void dialogue_baseinfo::clear_has_enum_msg() {
  _has_bits_[0] &= ~0x00000040u;
}
inline void dialogue_baseinfo::clear_enum_msg() {
  enum_msg_ = 0u;
  clear_has_enum_msg();
}
inline ::google::protobuf::uint32 dialogue_baseinfo::enum_msg() const {
  // @@protoc_insertion_point(field_get:c_dialogue_baseinfo.dialogue_baseinfo.enum_msg)
  return enum_msg_;
}
inline void dialogue_baseinfo::set_enum_msg(::google::protobuf::uint32 value) {
  set_has_enum_msg();
  enum_msg_ = value;
  // @@protoc_insertion_point(field_set:c_dialogue_baseinfo.dialogue_baseinfo.enum_msg)
}

// optional uint32 error_state = 2 [default = 0];
inline bool dialogue_baseinfo::has_error_state() const {
  return (_has_bits_[0] & 0x00000080u) != 0;
}
inline void dialogue_baseinfo::set_has_error_state() {
  _has_bits_[0] |= 0x00000080u;
}
inline void dialogue_baseinfo::clear_has_error_state() {
  _has_bits_[0] &= ~0x00000080u;
}
inline void dialogue_baseinfo::clear_error_state() {
  error_state_ = 0u;
  clear_has_error_state();
}
inline ::google::protobuf::uint32 dialogue_baseinfo::error_state() const {
  // @@protoc_insertion_point(field_get:c_dialogue_baseinfo.dialogue_baseinfo.error_state)
  return error_state_;
}
inline void dialogue_baseinfo::set_error_state(::google::protobuf::uint32 value) {
  set_has_error_state();
  error_state_ = value;
  // @@protoc_insertion_point(field_set:c_dialogue_baseinfo.dialogue_baseinfo.error_state)
}

// optional string error_desc = 3 [default = ""];
inline bool dialogue_baseinfo::has_error_desc() const {
  return (_has_bits_[0] & 0x00000001u) != 0;
}
inline void dialogue_baseinfo::set_has_error_desc() {
  _has_bits_[0] |= 0x00000001u;
}
inline void dialogue_baseinfo::clear_has_error_desc() {
  _has_bits_[0] &= ~0x00000001u;
}
inline void dialogue_baseinfo::clear_error_desc() {
  error_desc_.ClearToEmptyNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
  clear_has_error_desc();
}
inline const ::std::string& dialogue_baseinfo::error_desc() const {
  // @@protoc_insertion_point(field_get:c_dialogue_baseinfo.dialogue_baseinfo.error_desc)
  return error_desc_.GetNoArena();
}
inline void dialogue_baseinfo::set_error_desc(const ::std::string& value) {
  set_has_error_desc();
  error_desc_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), value);
  // @@protoc_insertion_point(field_set:c_dialogue_baseinfo.dialogue_baseinfo.error_desc)
}
#if LANG_CXX11
inline void dialogue_baseinfo::set_error_desc(::std::string&& value) {
  set_has_error_desc();
  error_desc_.SetNoArena(
    &::google::protobuf::internal::GetEmptyStringAlreadyInited(), ::std::move(value));
  // @@protoc_insertion_point(field_set_rvalue:c_dialogue_baseinfo.dialogue_baseinfo.error_desc)
}
#endif
inline void dialogue_baseinfo::set_error_desc(const char* value) {
  GOOGLE_DCHECK(value != NULL);
  set_has_error_desc();
  error_desc_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), ::std::string(value));
  // @@protoc_insertion_point(field_set_char:c_dialogue_baseinfo.dialogue_baseinfo.error_desc)
}
inline void dialogue_baseinfo::set_error_desc(const char* value, size_t size) {
  set_has_error_desc();
  error_desc_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(),
      ::std::string(reinterpret_cast<const char*>(value), size));
  // @@protoc_insertion_point(field_set_pointer:c_dialogue_baseinfo.dialogue_baseinfo.error_desc)
}
inline ::std::string* dialogue_baseinfo::mutable_error_desc() {
  set_has_error_desc();
  // @@protoc_insertion_point(field_mutable:c_dialogue_baseinfo.dialogue_baseinfo.error_desc)
  return error_desc_.MutableNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
}
inline ::std::string* dialogue_baseinfo::release_error_desc() {
  // @@protoc_insertion_point(field_release:c_dialogue_baseinfo.dialogue_baseinfo.error_desc)
  clear_has_error_desc();
  return error_desc_.ReleaseNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
}
inline void dialogue_baseinfo::set_allocated_error_desc(::std::string* error_desc) {
  if (error_desc != NULL) {
    set_has_error_desc();
  } else {
    clear_has_error_desc();
  }
  error_desc_.SetAllocatedNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), error_desc);
  // @@protoc_insertion_point(field_set_allocated:c_dialogue_baseinfo.dialogue_baseinfo.error_desc)
}

// optional uint64 user_id = 4 [default = 0];
inline bool dialogue_baseinfo::has_user_id() const {
  return (_has_bits_[0] & 0x00000100u) != 0;
}
inline void dialogue_baseinfo::set_has_user_id() {
  _has_bits_[0] |= 0x00000100u;
}
inline void dialogue_baseinfo::clear_has_user_id() {
  _has_bits_[0] &= ~0x00000100u;
}
inline void dialogue_baseinfo::clear_user_id() {
  user_id_ = GOOGLE_ULONGLONG(0);
  clear_has_user_id();
}
inline ::google::protobuf::uint64 dialogue_baseinfo::user_id() const {
  // @@protoc_insertion_point(field_get:c_dialogue_baseinfo.dialogue_baseinfo.user_id)
  return user_id_;
}
inline void dialogue_baseinfo::set_user_id(::google::protobuf::uint64 value) {
  set_has_user_id();
  user_id_ = value;
  // @@protoc_insertion_point(field_set:c_dialogue_baseinfo.dialogue_baseinfo.user_id)
}

// optional string user_nick = 5 [default = ""];
inline bool dialogue_baseinfo::has_user_nick() const {
  return (_has_bits_[0] & 0x00000002u) != 0;
}
inline void dialogue_baseinfo::set_has_user_nick() {
  _has_bits_[0] |= 0x00000002u;
}
inline void dialogue_baseinfo::clear_has_user_nick() {
  _has_bits_[0] &= ~0x00000002u;
}
inline void dialogue_baseinfo::clear_user_nick() {
  user_nick_.ClearToEmptyNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
  clear_has_user_nick();
}
inline const ::std::string& dialogue_baseinfo::user_nick() const {
  // @@protoc_insertion_point(field_get:c_dialogue_baseinfo.dialogue_baseinfo.user_nick)
  return user_nick_.GetNoArena();
}
inline void dialogue_baseinfo::set_user_nick(const ::std::string& value) {
  set_has_user_nick();
  user_nick_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), value);
  // @@protoc_insertion_point(field_set:c_dialogue_baseinfo.dialogue_baseinfo.user_nick)
}
#if LANG_CXX11
inline void dialogue_baseinfo::set_user_nick(::std::string&& value) {
  set_has_user_nick();
  user_nick_.SetNoArena(
    &::google::protobuf::internal::GetEmptyStringAlreadyInited(), ::std::move(value));
  // @@protoc_insertion_point(field_set_rvalue:c_dialogue_baseinfo.dialogue_baseinfo.user_nick)
}
#endif
inline void dialogue_baseinfo::set_user_nick(const char* value) {
  GOOGLE_DCHECK(value != NULL);
  set_has_user_nick();
  user_nick_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), ::std::string(value));
  // @@protoc_insertion_point(field_set_char:c_dialogue_baseinfo.dialogue_baseinfo.user_nick)
}
inline void dialogue_baseinfo::set_user_nick(const char* value, size_t size) {
  set_has_user_nick();
  user_nick_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(),
      ::std::string(reinterpret_cast<const char*>(value), size));
  // @@protoc_insertion_point(field_set_pointer:c_dialogue_baseinfo.dialogue_baseinfo.user_nick)
}
inline ::std::string* dialogue_baseinfo::mutable_user_nick() {
  set_has_user_nick();
  // @@protoc_insertion_point(field_mutable:c_dialogue_baseinfo.dialogue_baseinfo.user_nick)
  return user_nick_.MutableNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
}
inline ::std::string* dialogue_baseinfo::release_user_nick() {
  // @@protoc_insertion_point(field_release:c_dialogue_baseinfo.dialogue_baseinfo.user_nick)
  clear_has_user_nick();
  return user_nick_.ReleaseNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
}
inline void dialogue_baseinfo::set_allocated_user_nick(::std::string* user_nick) {
  if (user_nick != NULL) {
    set_has_user_nick();
  } else {
    clear_has_user_nick();
  }
  user_nick_.SetAllocatedNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), user_nick);
  // @@protoc_insertion_point(field_set_allocated:c_dialogue_baseinfo.dialogue_baseinfo.user_nick)
}

// optional string user_password = 6 [default = ""];
inline bool dialogue_baseinfo::has_user_password() const {
  return (_has_bits_[0] & 0x00000004u) != 0;
}
inline void dialogue_baseinfo::set_has_user_password() {
  _has_bits_[0] |= 0x00000004u;
}
inline void dialogue_baseinfo::clear_has_user_password() {
  _has_bits_[0] &= ~0x00000004u;
}
inline void dialogue_baseinfo::clear_user_password() {
  user_password_.ClearToEmptyNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
  clear_has_user_password();
}
inline const ::std::string& dialogue_baseinfo::user_password() const {
  // @@protoc_insertion_point(field_get:c_dialogue_baseinfo.dialogue_baseinfo.user_password)
  return user_password_.GetNoArena();
}
inline void dialogue_baseinfo::set_user_password(const ::std::string& value) {
  set_has_user_password();
  user_password_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), value);
  // @@protoc_insertion_point(field_set:c_dialogue_baseinfo.dialogue_baseinfo.user_password)
}
#if LANG_CXX11
inline void dialogue_baseinfo::set_user_password(::std::string&& value) {
  set_has_user_password();
  user_password_.SetNoArena(
    &::google::protobuf::internal::GetEmptyStringAlreadyInited(), ::std::move(value));
  // @@protoc_insertion_point(field_set_rvalue:c_dialogue_baseinfo.dialogue_baseinfo.user_password)
}
#endif
inline void dialogue_baseinfo::set_user_password(const char* value) {
  GOOGLE_DCHECK(value != NULL);
  set_has_user_password();
  user_password_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), ::std::string(value));
  // @@protoc_insertion_point(field_set_char:c_dialogue_baseinfo.dialogue_baseinfo.user_password)
}
inline void dialogue_baseinfo::set_user_password(const char* value, size_t size) {
  set_has_user_password();
  user_password_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(),
      ::std::string(reinterpret_cast<const char*>(value), size));
  // @@protoc_insertion_point(field_set_pointer:c_dialogue_baseinfo.dialogue_baseinfo.user_password)
}
inline ::std::string* dialogue_baseinfo::mutable_user_password() {
  set_has_user_password();
  // @@protoc_insertion_point(field_mutable:c_dialogue_baseinfo.dialogue_baseinfo.user_password)
  return user_password_.MutableNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
}
inline ::std::string* dialogue_baseinfo::release_user_password() {
  // @@protoc_insertion_point(field_release:c_dialogue_baseinfo.dialogue_baseinfo.user_password)
  clear_has_user_password();
  return user_password_.ReleaseNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
}
inline void dialogue_baseinfo::set_allocated_user_password(::std::string* user_password) {
  if (user_password != NULL) {
    set_has_user_password();
  } else {
    clear_has_user_password();
  }
  user_password_.SetAllocatedNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), user_password);
  // @@protoc_insertion_point(field_set_allocated:c_dialogue_baseinfo.dialogue_baseinfo.user_password)
}

// optional uint64 to_user_id = 7 [default = 0];
inline bool dialogue_baseinfo::has_to_user_id() const {
  return (_has_bits_[0] & 0x00000200u) != 0;
}
inline void dialogue_baseinfo::set_has_to_user_id() {
  _has_bits_[0] |= 0x00000200u;
}
inline void dialogue_baseinfo::clear_has_to_user_id() {
  _has_bits_[0] &= ~0x00000200u;
}
inline void dialogue_baseinfo::clear_to_user_id() {
  to_user_id_ = GOOGLE_ULONGLONG(0);
  clear_has_to_user_id();
}
inline ::google::protobuf::uint64 dialogue_baseinfo::to_user_id() const {
  // @@protoc_insertion_point(field_get:c_dialogue_baseinfo.dialogue_baseinfo.to_user_id)
  return to_user_id_;
}
inline void dialogue_baseinfo::set_to_user_id(::google::protobuf::uint64 value) {
  set_has_to_user_id();
  to_user_id_ = value;
  // @@protoc_insertion_point(field_set:c_dialogue_baseinfo.dialogue_baseinfo.to_user_id)
}

// optional string to_user_nick = 8 [default = ""];
inline bool dialogue_baseinfo::has_to_user_nick() const {
  return (_has_bits_[0] & 0x00000008u) != 0;
}
inline void dialogue_baseinfo::set_has_to_user_nick() {
  _has_bits_[0] |= 0x00000008u;
}
inline void dialogue_baseinfo::clear_has_to_user_nick() {
  _has_bits_[0] &= ~0x00000008u;
}
inline void dialogue_baseinfo::clear_to_user_nick() {
  to_user_nick_.ClearToEmptyNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
  clear_has_to_user_nick();
}
inline const ::std::string& dialogue_baseinfo::to_user_nick() const {
  // @@protoc_insertion_point(field_get:c_dialogue_baseinfo.dialogue_baseinfo.to_user_nick)
  return to_user_nick_.GetNoArena();
}
inline void dialogue_baseinfo::set_to_user_nick(const ::std::string& value) {
  set_has_to_user_nick();
  to_user_nick_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), value);
  // @@protoc_insertion_point(field_set:c_dialogue_baseinfo.dialogue_baseinfo.to_user_nick)
}
#if LANG_CXX11
inline void dialogue_baseinfo::set_to_user_nick(::std::string&& value) {
  set_has_to_user_nick();
  to_user_nick_.SetNoArena(
    &::google::protobuf::internal::GetEmptyStringAlreadyInited(), ::std::move(value));
  // @@protoc_insertion_point(field_set_rvalue:c_dialogue_baseinfo.dialogue_baseinfo.to_user_nick)
}
#endif
inline void dialogue_baseinfo::set_to_user_nick(const char* value) {
  GOOGLE_DCHECK(value != NULL);
  set_has_to_user_nick();
  to_user_nick_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), ::std::string(value));
  // @@protoc_insertion_point(field_set_char:c_dialogue_baseinfo.dialogue_baseinfo.to_user_nick)
}
inline void dialogue_baseinfo::set_to_user_nick(const char* value, size_t size) {
  set_has_to_user_nick();
  to_user_nick_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(),
      ::std::string(reinterpret_cast<const char*>(value), size));
  // @@protoc_insertion_point(field_set_pointer:c_dialogue_baseinfo.dialogue_baseinfo.to_user_nick)
}
inline ::std::string* dialogue_baseinfo::mutable_to_user_nick() {
  set_has_to_user_nick();
  // @@protoc_insertion_point(field_mutable:c_dialogue_baseinfo.dialogue_baseinfo.to_user_nick)
  return to_user_nick_.MutableNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
}
inline ::std::string* dialogue_baseinfo::release_to_user_nick() {
  // @@protoc_insertion_point(field_release:c_dialogue_baseinfo.dialogue_baseinfo.to_user_nick)
  clear_has_to_user_nick();
  return to_user_nick_.ReleaseNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
}
inline void dialogue_baseinfo::set_allocated_to_user_nick(::std::string* to_user_nick) {
  if (to_user_nick != NULL) {
    set_has_to_user_nick();
  } else {
    clear_has_to_user_nick();
  }
  to_user_nick_.SetAllocatedNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), to_user_nick);
  // @@protoc_insertion_point(field_set_allocated:c_dialogue_baseinfo.dialogue_baseinfo.to_user_nick)
}

// optional string talking = 9 [default = ""];
inline bool dialogue_baseinfo::has_talking() const {
  return (_has_bits_[0] & 0x00000010u) != 0;
}
inline void dialogue_baseinfo::set_has_talking() {
  _has_bits_[0] |= 0x00000010u;
}
inline void dialogue_baseinfo::clear_has_talking() {
  _has_bits_[0] &= ~0x00000010u;
}
inline void dialogue_baseinfo::clear_talking() {
  talking_.ClearToEmptyNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
  clear_has_talking();
}
inline const ::std::string& dialogue_baseinfo::talking() const {
  // @@protoc_insertion_point(field_get:c_dialogue_baseinfo.dialogue_baseinfo.talking)
  return talking_.GetNoArena();
}
inline void dialogue_baseinfo::set_talking(const ::std::string& value) {
  set_has_talking();
  talking_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), value);
  // @@protoc_insertion_point(field_set:c_dialogue_baseinfo.dialogue_baseinfo.talking)
}
#if LANG_CXX11
inline void dialogue_baseinfo::set_talking(::std::string&& value) {
  set_has_talking();
  talking_.SetNoArena(
    &::google::protobuf::internal::GetEmptyStringAlreadyInited(), ::std::move(value));
  // @@protoc_insertion_point(field_set_rvalue:c_dialogue_baseinfo.dialogue_baseinfo.talking)
}
#endif
inline void dialogue_baseinfo::set_talking(const char* value) {
  GOOGLE_DCHECK(value != NULL);
  set_has_talking();
  talking_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), ::std::string(value));
  // @@protoc_insertion_point(field_set_char:c_dialogue_baseinfo.dialogue_baseinfo.talking)
}
inline void dialogue_baseinfo::set_talking(const char* value, size_t size) {
  set_has_talking();
  talking_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(),
      ::std::string(reinterpret_cast<const char*>(value), size));
  // @@protoc_insertion_point(field_set_pointer:c_dialogue_baseinfo.dialogue_baseinfo.talking)
}
inline ::std::string* dialogue_baseinfo::mutable_talking() {
  set_has_talking();
  // @@protoc_insertion_point(field_mutable:c_dialogue_baseinfo.dialogue_baseinfo.talking)
  return talking_.MutableNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
}
inline ::std::string* dialogue_baseinfo::release_talking() {
  // @@protoc_insertion_point(field_release:c_dialogue_baseinfo.dialogue_baseinfo.talking)
  clear_has_talking();
  return talking_.ReleaseNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
}
inline void dialogue_baseinfo::set_allocated_talking(::std::string* talking) {
  if (talking != NULL) {
    set_has_talking();
  } else {
    clear_has_talking();
  }
  talking_.SetAllocatedNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), talking);
  // @@protoc_insertion_point(field_set_allocated:c_dialogue_baseinfo.dialogue_baseinfo.talking)
}

// optional uint32 system_state = 10 [default = 0];
inline bool dialogue_baseinfo::has_system_state() const {
  return (_has_bits_[0] & 0x00000400u) != 0;
}
inline void dialogue_baseinfo::set_has_system_state() {
  _has_bits_[0] |= 0x00000400u;
}
inline void dialogue_baseinfo::clear_has_system_state() {
  _has_bits_[0] &= ~0x00000400u;
}
inline void dialogue_baseinfo::clear_system_state() {
  system_state_ = 0u;
  clear_has_system_state();
}
inline ::google::protobuf::uint32 dialogue_baseinfo::system_state() const {
  // @@protoc_insertion_point(field_get:c_dialogue_baseinfo.dialogue_baseinfo.system_state)
  return system_state_;
}
inline void dialogue_baseinfo::set_system_state(::google::protobuf::uint32 value) {
  set_has_system_state();
  system_state_ = value;
  // @@protoc_insertion_point(field_set:c_dialogue_baseinfo.dialogue_baseinfo.system_state)
}

// optional string system_desc = 11 [default = ""];
inline bool dialogue_baseinfo::has_system_desc() const {
  return (_has_bits_[0] & 0x00000020u) != 0;
}
inline void dialogue_baseinfo::set_has_system_desc() {
  _has_bits_[0] |= 0x00000020u;
}
inline void dialogue_baseinfo::clear_has_system_desc() {
  _has_bits_[0] &= ~0x00000020u;
}
inline void dialogue_baseinfo::clear_system_desc() {
  system_desc_.ClearToEmptyNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
  clear_has_system_desc();
}
inline const ::std::string& dialogue_baseinfo::system_desc() const {
  // @@protoc_insertion_point(field_get:c_dialogue_baseinfo.dialogue_baseinfo.system_desc)
  return system_desc_.GetNoArena();
}
inline void dialogue_baseinfo::set_system_desc(const ::std::string& value) {
  set_has_system_desc();
  system_desc_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), value);
  // @@protoc_insertion_point(field_set:c_dialogue_baseinfo.dialogue_baseinfo.system_desc)
}
#if LANG_CXX11
inline void dialogue_baseinfo::set_system_desc(::std::string&& value) {
  set_has_system_desc();
  system_desc_.SetNoArena(
    &::google::protobuf::internal::GetEmptyStringAlreadyInited(), ::std::move(value));
  // @@protoc_insertion_point(field_set_rvalue:c_dialogue_baseinfo.dialogue_baseinfo.system_desc)
}
#endif
inline void dialogue_baseinfo::set_system_desc(const char* value) {
  GOOGLE_DCHECK(value != NULL);
  set_has_system_desc();
  system_desc_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), ::std::string(value));
  // @@protoc_insertion_point(field_set_char:c_dialogue_baseinfo.dialogue_baseinfo.system_desc)
}
inline void dialogue_baseinfo::set_system_desc(const char* value, size_t size) {
  set_has_system_desc();
  system_desc_.SetNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(),
      ::std::string(reinterpret_cast<const char*>(value), size));
  // @@protoc_insertion_point(field_set_pointer:c_dialogue_baseinfo.dialogue_baseinfo.system_desc)
}
inline ::std::string* dialogue_baseinfo::mutable_system_desc() {
  set_has_system_desc();
  // @@protoc_insertion_point(field_mutable:c_dialogue_baseinfo.dialogue_baseinfo.system_desc)
  return system_desc_.MutableNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
}
inline ::std::string* dialogue_baseinfo::release_system_desc() {
  // @@protoc_insertion_point(field_release:c_dialogue_baseinfo.dialogue_baseinfo.system_desc)
  clear_has_system_desc();
  return system_desc_.ReleaseNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited());
}
inline void dialogue_baseinfo::set_allocated_system_desc(::std::string* system_desc) {
  if (system_desc != NULL) {
    set_has_system_desc();
  } else {
    clear_has_system_desc();
  }
  system_desc_.SetAllocatedNoArena(&::google::protobuf::internal::GetEmptyStringAlreadyInited(), system_desc);
  // @@protoc_insertion_point(field_set_allocated:c_dialogue_baseinfo.dialogue_baseinfo.system_desc)
}

#endif  // !PROTOBUF_INLINE_NOT_IN_HEADERS

// @@protoc_insertion_point(namespace_scope)


}  // namespace c_dialogue_baseinfo

// @@protoc_insertion_point(global_scope)

#endif  // PROTOBUF_c_5fdialogue_5fbaseinfo_2eproto__INCLUDED