export const arraysAreEqual = (arr1: number[], arr2: number[]): boolean => {
    if (arr1.length !== arr2.length) return false;
    return arr1.every((value, index) => value === arr2[index]);
  };
  
  
  // export const arraysAreEqual:(arr1:any[], arr2:any[])=> boolean = (arr1: any[], arr2: any[]): boolean => {
  //   if(arr1.length !== arr2.length) return false; // Check if length are the same
  // return arr1.every((value: any, index: number):boolean => value === arr2[index]); // Check if not length same
  //}