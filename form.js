function determineOSAndEnv(operating_system, availability) {
  // Parse the operating_system to an integer
  operating_system = parseInt(operating_system);

  let os;
  let env;
  let sla;
  let osVersion;

  if (operating_system >= 1 && operating_system <= 4) {
    os = "2022";
    osVersion = "2022";
  } else if (operating_system >= 5 && operating_system <= 8) {
    os = "2019";
    osVersion = "2019";
  } else if (operating_system >= 9 && operating_system <= 12) {
    os = "linux9";
    osVersion = '9';
  } else if (operating_system >= 13 && operating_system <= 16) {
    os = "linux8";
    osVersion = '8';
  }

  if ([2, 6, 10, 14].includes(operating_system)) {
    env = "Test";
  } else if ([1, 5, 9, 13].includes(operating_system)) {
    env = "Prod";
  } else if ([3, 7, 11, 15].includes(operating_system)) {
    env = "Dev";
  } else if ([4, 8, 12, 16].includes(operating_system)) {
    env = "ACC";
  }

// Set sla based on availability
  if (availability === '1') {
    sla = 'bronze';
  } else if (availability === '2') {
    sla = 'silver';
  } else if (availability === '3') {
    sla = 'gold';
  } else if (availability === '4') {
    sla = 'platinum';
  }
  return { os, env, sla, osVersion };
}

// Determine the OS and SLA values
const result = determineOSAndEnv(values['operating_system'], values['availability']);
const os = result.os;
const env = result.env;
const sla = result.sla;
const zone = values['network_zone']
const osv = result.osVersion;

// Function to update the selected option in a select element
function setSelectedOption(selectElement, value) {
  for (let option of selectElement.options) {
    if (option.value === value) {
      option.selected = true;
    } else {
      option.selected = false;
    }
  }
}

// Get the first form element on the page
const myForm = document.forms[0];

// Walk into the form and find the select elements by their name attributes
if (myForm) {

  const operatingSystemVersionSelect = myForm.elements['operating_system_version'];
  const vmEnvironmentNameSelect = myForm.elements['vm_environment_name'];
  const vmAvailabilityTypeSelect = myForm.elements['vm_availability_type'];
  const vm_selected_storage = myForm.elements['vm_selected_storage'];
  const vm_network_name = myForm.elements['vm_network_name'];
  const vm_environment_name = myForm.elements['vm_environment_name'];
  const vm_storage_name = myForm.elements['vm_storage_network_name'];

  // Check if the "SELECT" list name is "vm_storage_network_name" and update the selected option
  if (vm_storage_name) {




  }

  // Check if the "SELECT" list name is "operating_system_version" and update the selected option
  if (vm_selected_storage) {


    

  }

  // Check if the "SELECT" list name is "operating_system_version" and update the selected option
  if (vm_network_name) {

      
      

  }


  // Check if the "SELECT" list name is "operating_system_version" and update the selected option
  if (vm_environment_name) {
        if(env=="Prod"){
                setSelectedOption(vm_environment_name, "PRD");

        }
        if(env=="Test"){
                setSelectedOption(vm_environment_name, "TST");
        }

        if(env=="Dev"){
                setSelectedOption(vm_environment_name, "DEV");
        }

        if(env=="ACC"){
                setSelectedOption(vm_environment_name, "ACC");
        }
  }

  // Check if the "SELECT" list name is "operating_system_version" and update the selected option
  if (operatingSystemVersionSelect) {
    setSelectedOption(operatingSystemVersionSelect, os);
  }

  // Check if the list name is "vm_availability_type" and update the selected option
  if (vmAvailabilityTypeSelect) {
    setSelectedOption(vmAvailabilityTypeSelect, sla);
  }
}

// Function to set the value of an input field
function setInputValue(inputElement, value) {
try {
 inputElement.value = value;
} catch (error) {
//alert(inputElement);
//alert(value);  // Code to handle the error
}
}

// Get the input elements and set their values based on the `values` object
const slaNumberInput = document.getElementsByName('sla_number')[0];
const vmMemorySizeInput = document.getElementsByName('_int_vm_memory_size')[0];
const vmNumberVCPUInput = document.getElementsByName('_int_vm_number_vcpu')[0];
const vmMemorySizeInputLin = document.getElementsByName('vm_memory_size')[0];
const vmNumberVCPUInputLin = document.getElementsByName('vm_number_vcpu')[0];
const networkZoneInput = document.getElementsByName('network_zone')[0];
const tierNumberInput = document.getElementsByName('tier_number')[0];
const description = document.getElementsByName('description')[0].substring(0, 30);
const inc = document.getElementsByName('remedy_incident_id')[0];
const machine_name = document.getElementsByName('machine_name')[0];
const order_email = document.getElementsByName('order_email')[0];
const osVersionNr = document.getElementsByName('operating_system_version')[0];

setInputValue(slaNumberInput, values.sla_number);
setInputValue(vmMemorySizeInput, values.vm_memory_size);
setInputValue(vmNumberVCPUInput, values.vm_number_vcpu);
setInputValue(vmMemorySizeInputLin, values.vm_memory_size);
setInputValue(vmNumberVCPUInputLin, values.vm_number_vcpu);
setInputValue(networkZoneInput, values.network_zone);
setInputValue(tierNumberInput, values.tier_number);
setInputValue(description, decodeURI(values.description));
setInputValue(inc, values.inc);
setInputValue(machine_name, "");
setInputValue(order_email, values.email);
setInputValue(osVersionNr, osv);
