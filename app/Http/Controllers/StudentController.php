<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
class StudentController extends Controller
{
    //
    public function index(){
        $students = Student::all();
        return view('students', compact('students'));
    }

    public function destroy($id){
        $student= Student::findOrFail($id);
        if($student){
            $student->delete();
            return redirect()->route('students.index')->with('success','Student deleted successfully');
        }
        return redirect()->route('students.index')->with('error','Student not found');
    }
}
