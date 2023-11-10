<?php

namespace App\Models\Traits\Helpers;

use App\Models\{MasterRole, User};
use Illuminate\Support\Facades\Hash;

trait Accountable
{
  public function updateUserRules(array $rules, User $theUser, array $data)
  {
    $theUserRole = $theUser->userRole->role->role_name;
    unset($rules["password"], $rules["password_confirmation"], $rules["username"], $rules["email"]);
    if ($theUserRole === "officer") $rules['role'] = ["required"];
    if ($data["nik"] === $theUser->nik) unset($rules["nik"]);
    if (!array_key_exists("nip", $data)) unset($rules["nip"]);
    if (!array_key_exists("nisn", $data)) unset($rules["nisn"]);
    if (array_key_exists("nip", $data)) if ($data["nip"] === $theUser->officer?->nip) unset($rules["nip"]);
    if (array_key_exists("nisn", $data)) if ($data["nisn"] === $theUser->student?->nisn) unset($rules["nisn"]);
    return $rules;
  }
  public function updateUserUniqueAndRole(User $user, User $theUser, array $credentials)
  {
    // ---------------------------------
    // Role validations
    $theUserRole = $theUser->userRole->role->role_name;

    // Officer
    if ($theUserRole === "officer") {
      // Role
      $inputRole = $credentials["role"];
      $credentials["role"] = MasterRole::where("role_name", $credentials["role"])->value("id_role");

      // Update unique
      if (array_key_exists("nip", $credentials))
        $theUser->officer()->update([
          "nip" => $credentials["nip"],
          "updated_by" => $user->id_user,
        ]);

      // Update role
      if ($theUserRole !== $inputRole)
        $theUser->userRole()->update(["id_role" => $credentials["role"]]);
    }

    // Student
    if ($theUserRole === "student")
      // Update unique
      if (array_key_exists("nisn", $credentials))
        $theUser->student()->update([
          "nisn" => $credentials["nisn"],
          "updated_by" => $user->id_user,
        ]);

    // Update the user
    $credentials["updated_by"] = $user->user_id;
    $theUser->update($credentials);

    // Success
    $theUser->refresh();
    return redirect("/dashboard/users")->withSuccess("Pengguna @$theUser->username berhasil diubah.");
  }
  public function isYourAccount(User $yourAccount, User $user, string $message = "Akun tidak ditemukan.")
  {
    if ($yourAccount->id_user !== $user->id_user) throw new \Exception($message);
  }
  public function createRoleUser(User $theUser, array $credentials)
  {
    $theUser->userRole()->create(["id_role" => $credentials["role"]]);
    return $theUser;
  }
  public function createUniqueUser(User $theUser, string $roleName, array $credentials)
  {
    if ($roleName === "officer")
      $theUser->officer()->update([
        "nip" => $credentials["nip"],
      ]);
    if ($roleName === "student")
      $theUser->student()->update([
        "nisn" => $credentials["nisn"],
      ]);

    return $theUser;
  }
  public function checkCurrentPassword(User $user, string $currentPassword)
  {
    if (!Hash::check($currentPassword, $user->password))
      throw new \Exception("Password saat ini salah. Silakan coba lagi.");
  }
  public function checkSamePassword(string $currentPassword, string $newPassword)
  {
    if ($currentPassword === $newPassword)
      throw new \Exception("Password baru tidak boleh sama dengan password lama.");
  }
}