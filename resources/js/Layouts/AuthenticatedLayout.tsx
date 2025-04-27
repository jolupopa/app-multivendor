import ApplicationLogo from '@/Components/App/ApplicationLogo';
import Dropdown from '@/Components/Core/Dropdown';
import NavLink from '@/Components/Core/NavLink';
import ResponsiveNavLink from '@/Components/Core/ResponsiveNavLink';
import Navbar from "@/Components/App/Navbar";
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useEffect, useState, useRef } from 'react';

export default function AuthenticatedLayout({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {

    const props = usePage().props;

    const user = usePage().props.auth.user;

    const [successMessages, setSuccessMessages] = useState<any[]>([]);

    const timeoutRefs = useRef<{ [key: number]: ReturnType<typeof setTimeout> }>({}); // Store timeouts by message ID

    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

    useEffect(() => {
      if (props.success.message) {
        const newMessage = {
          ...props.success,
          id: props.success.time, //Use time as unique identifier
        };
        // Add the new message to the list
        setSuccessMessages((prevMessages) => [newMessage, ...prevMessages]);

        // Set a time for this specific message
        const timeoutID = setTimeout( () => {
          // use a funcional update to ensure the latest state is used
          setSuccessMessages((prevMessages) =>
            prevMessages.filter((msg) => msg.id !== newMessage.id )
          );
          // clear timeout from refs after execution
          delete timeoutRefs.current[newMessage.id];
        }, 5000);

        // Store the timeout Id in the ref
        timeoutRefs.current[newMessage.id] = timeoutID;

      }

    }, [props.success]);

    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
            <Navbar />

            {/* {header && (
                <header className="bg-white shadow dark:bg-gray-800">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )} */}

            {props.error && (
              <div className='container mx-auto px-8 mt-8'>
                <div className='alert alert-error'>
                  {props.error}
                </div>
              </div>
            )}

            {successMessages.length > 0 && (
              <div className='toast toast-top toast-end z-[1000] mt-16'>
                {successMessages.map((msg) => (
                  <div className='alert alert-success' key={msg.id}>
                    <span>{msg.message}</span>
                  </div>
                ))}
              </div>

            )}

            <main>{children}</main>
        </div>
    );
}

