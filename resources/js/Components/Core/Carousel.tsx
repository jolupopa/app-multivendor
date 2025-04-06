import { Image } from "@/types";
import {useEffect, useState } from "react"

function Carousel({images}: { images: Image[] }): JSX.Element {

  const [selectedImage, setSelectedImage] = useState<Image>(images[0]);

  useEffect((): void => {
    if (images && images.length > 0) {
      setSelectedImage(images[0]);
    }
  }, [images.length]);

  return (
    <>
      <div className="flex items-start gap-8">
        <div className="flex flex-col items-center gap-2 py-2">
          {images.map((image:Image, i: number) => (
                <button onClick={ () => setSelectedImage(image)}
                className=
                  {"border-2 " +
                    (selectedImage.id === image.id ? 'border-blue-500' : 'hover:border-blue-500')
                  }
                key={image.id} >
              <img src={image.thumb} alt="" className="w-[50px]" />
            </button>
          ))}
        </div>
        <div className="carousel w-full">
            <div className="carousel-item w-full" >
              <img src={selectedImage.large} alt="" className="w-full" />
            </div>
        </div>
      </div>
    </>
  );
}

export default Carousel;
